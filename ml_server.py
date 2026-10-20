"""
ml_server.py  —  Flask ML API for Sales & Demand Forecasting
Run:  python ml_server.py
Listens on http://localhost:5001
"""

from flask import Flask, jsonify
from flask_cors import CORS
import mysql.connector
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import warnings
warnings.filterwarnings('ignore')

# ── CONFIG ─────────────────────────────────────────────────────
DB_CONFIG = {
    'host':     'localhost',
    'user':     'root',
    'password': '',
    'database': 'reks_system'
}

app = Flask(__name__)
CORS(app)

# ── DB HELPER ───────────────────────────────────────────────────
def get_df(query, params=None):
    conn = mysql.connector.connect(**DB_CONFIG)
    df = pd.read_sql(query, conn, params=params)
    conn.close()
    return df

# ═══════════════════════════════════════════════════════════════
#  /api/top_products
# ═══════════════════════════════════════════════════════════════
@app.route('/api/top_products')
def top_products():
    query = """
        SELECT
            p.product_name,
            p.category_name,
            SUM(ti.quantity) AS total_qty,
            SUM(ti.subtotal) AS total_revenue
        FROM transaction_items ti
        JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
        JOIN products p ON ti.product_id = p.product_id
        WHERE st.transaction_status = 'Completed'
        GROUP BY p.product_id, p.product_name, p.category_name
        ORDER BY total_qty DESC
        LIMIT 15
    """
    df = get_df(query)

    query_month = """
        SELECT
            p.product_name,
            p.category_name,
            SUM(ti.quantity) AS total_qty,
            SUM(ti.subtotal) AS total_revenue
        FROM transaction_items ti
        JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
        JOIN products p ON ti.product_id = p.product_id
        WHERE st.transaction_status = 'Completed'
          AND MONTH(st.created_at) = MONTH(CURDATE())
          AND YEAR(st.created_at)  = YEAR(CURDATE())
        GROUP BY p.product_id, p.product_name, p.category_name
        ORDER BY total_qty DESC
        LIMIT 10
    """
    df_month = get_df(query_month)

    return jsonify({
        'all_time':   df.to_dict(orient='records'),
        'this_month': df_month.to_dict(orient='records')
    })

# ═══════════════════════════════════════════════════════════════
#  /api/day_of_week
# ═══════════════════════════════════════════════════════════════
@app.route('/api/day_of_week')
def day_of_week():
    query = """
        SELECT
            DAYOFWEEK(st.created_at)          AS dow_num,
            DAYNAME(st.created_at)            AS day_name,
            COUNT(DISTINCT st.transaction_id) AS num_transactions,
            SUM(ti.quantity)                  AS total_qty,
            SUM(st.total_amount)              AS total_revenue
        FROM sales_transactions st
        JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
        WHERE st.transaction_status = 'Completed'
        GROUP BY dow_num, day_name
        ORDER BY dow_num
    """
    df = get_df(query)

    query_weekend = """
        SELECT
            p.product_name,
            p.category_name,
            DAYNAME(st.created_at) AS day_name,
            SUM(ti.quantity)       AS total_qty
        FROM transaction_items ti
        JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
        JOIN products p ON ti.product_id = p.product_id
        WHERE st.transaction_status = 'Completed'
          AND DAYOFWEEK(st.created_at) IN (1, 7)
        GROUP BY p.product_id, p.product_name, p.category_name, DAYNAME(st.created_at)
        ORDER BY total_qty DESC
        LIMIT 20
    """
    df_weekend = get_df(query_weekend)

    return jsonify({
        'by_day':      df.to_dict(orient='records'),
        'weekend_top': df_weekend.to_dict(orient='records')
    })

# ═══════════════════════════════════════════════════════════════
#  /api/hourly_peaks
# ═══════════════════════════════════════════════════════════════
@app.route('/api/hourly_peaks')
def hourly_peaks():
    query = """
        SELECT
            HOUR(st.created_at)               AS hour,
            COUNT(DISTINCT st.transaction_id) AS num_transactions,
            SUM(ti.quantity)                  AS total_items,
            SUM(st.total_amount)              AS total_revenue
        FROM sales_transactions st
        JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
        WHERE st.transaction_status = 'Completed'
        GROUP BY HOUR(st.created_at)
        ORDER BY hour
    """
    df = get_df(query)

    if df.empty:
        return jsonify({'hourly': [], 'peak_hours': [], 'manpower_advice': []})

    threshold = df['num_transactions'].quantile(0.70)
    peak_rows = df[df['num_transactions'] >= threshold]

    def hour_label(h):
        suffix = 'AM' if h < 12 else 'PM'
        display = h if h <= 12 else h - 12
        if display == 0: display = 12
        return f"{display}:00 {suffix}"

    peak_hours = [hour_label(int(r['hour'])) for _, r in peak_rows.iterrows()]

    advice = []
    for _, r in peak_rows.iterrows():
        h   = int(r['hour'])
        txn = int(r['num_transactions'])
        staff_needed = max(1, round(txn / 10))
        advice.append({
            'hour':              hour_label(h),
            'transactions':      txn,
            'recommended_staff': staff_needed,
            'note': f"Deploy {staff_needed} cashier(s) — avg {txn} transactions at this hour"
        })

    query_cat = """
        SELECT
            HOUR(st.created_at) AS hour,
            p.category_name,
            SUM(ti.quantity)    AS total_qty
        FROM transaction_items ti
        JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
        JOIN products p ON ti.product_id = p.product_id
        WHERE st.transaction_status = 'Completed'
        GROUP BY HOUR(st.created_at), p.category_name
        ORDER BY hour, total_qty DESC
    """
    df_cat = get_df(query_cat)

    return jsonify({
        'hourly':          df.to_dict(orient='records'),
        'peak_hours':      peak_hours,
        'manpower_advice': advice,
        'by_category':     df_cat.to_dict(orient='records')
    })

# ═══════════════════════════════════════════════════════════════
#  /api/next_month_forecast
# ═══════════════════════════════════════════════════════════════
@app.route('/api/next_month_forecast')
def next_month_forecast():
    query = """
        SELECT
            p.product_id,
            p.product_name,
            p.category_name,
            DATE(st.created_at) AS sale_date,
            SUM(ti.quantity)    AS daily_qty
        FROM transaction_items ti
        JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
        JOIN products p ON ti.product_id = p.product_id
        WHERE st.transaction_status = 'Completed'
          AND st.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        GROUP BY p.product_id, DATE(st.created_at)
        ORDER BY p.product_id, sale_date
    """
    df = get_df(query)

    if df.empty:
        return jsonify({'forecasts': [], 'note': 'Not enough data yet.',
                        'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S')})

    df['sale_date'] = pd.to_datetime(df['sale_date'])
    results = []
    today   = pd.Timestamp(datetime.today().date())

    for prod_id, group in df.groupby('product_id'):
        group = group.sort_values('sale_date').copy()
        name  = group['product_name'].iloc[0]
        cat   = group['category_name'].iloc[0]

        group['day_num'] = (group['sale_date'] - group['sale_date'].min()).dt.days
        X = group['day_num'].values
        y = group['daily_qty'].values

        if len(X) < 3:
            continue

        coeffs = np.polyfit(X, y, 1)
        slope, intercept = coeffs

        last_day    = X[-1]
        future_days = np.arange(last_day + 1, last_day + 31)
        predictions = np.maximum(np.polyval(coeffs, future_days), 0)

        forecast_monthly   = float(predictions.sum())
        forecast_daily_avg = float(predictions.mean())
        trend = 'up' if slope > 0.05 else ('down' if slope < -0.05 else 'stable')

        this_month_data = group[group['sale_date'].dt.month == today.month]['daily_qty']
        this_month_avg  = float(this_month_data.mean()) if not this_month_data.empty else 0.0

        results.append({
            'product_name':        name,
            'category_name':       cat,
            'forecast_next_month': round(forecast_monthly),
            'forecast_daily_avg':  round(forecast_daily_avg, 1),
            'current_month_avg':   round(this_month_avg, 1),
            'trend':               trend,
            'data_points':         len(X)
        })

    results.sort(key=lambda x: x['forecast_next_month'], reverse=True)

    return jsonify({
        'forecasts':    results[:20],
        'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    })

# ═══════════════════════════════════════════════════════════════
#  /api/monthly_trend
# ═══════════════════════════════════════════════════════════════
@app.route('/api/monthly_trend')
def monthly_trend():
    query = """
        SELECT
            DATE_FORMAT(st.created_at, '%Y-%m') AS month,
            COUNT(DISTINCT st.transaction_id)   AS num_transactions,
            SUM(ti.quantity)                    AS total_qty,
            SUM(st.total_amount)                AS total_revenue
        FROM sales_transactions st
        JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
        WHERE st.transaction_status = 'Completed'
        GROUP BY DATE_FORMAT(st.created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    """
    df = get_df(query)
    df = df.iloc[::-1]

    return jsonify({'monthly': df.to_dict(orient='records')})

# ═══════════════════════════════════════════════════════════════
#  /api/summary
# ═══════════════════════════════════════════════════════════════
@app.route('/api/summary')
def summary():
    query = """
        SELECT
            COUNT(DISTINCT st.transaction_id) AS total_txn,
            SUM(st.total_amount)              AS total_revenue,
            SUM(ti.quantity)                  AS total_items_sold
        FROM sales_transactions st
        JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
        WHERE st.transaction_status = 'Completed'
          AND MONTH(st.created_at) = MONTH(CURDATE())
          AND YEAR(st.created_at)  = YEAR(CURDATE())
    """
    df  = get_df(query)
    row = df.iloc[0].to_dict() if not df.empty else {}

    query2 = """
        SELECT COUNT(*) AS low_stock_count
        FROM products
        WHERE stock <= 20
          AND status = 'active'
          AND category_name NOT IN ('Rides','Massage','Billiards','Rentals')
    """
    df2 = get_df(query2)
    row['low_stock_count'] = int(df2.iloc[0]['low_stock_count']) if not df2.empty else 0

    for k, v in row.items():
        if hasattr(v, 'item'):
            row[k] = v.item()
        elif v is None:
            row[k] = 0

    return jsonify(row)

# ── MAIN ────────────────────────────────────────────────────────
if __name__ == '__main__':
    print("✅ ML Server running at http://localhost:5001")
    app.run(host='0.0.0.0', port=5001, debug=False)