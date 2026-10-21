<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SystemLogger;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InventoryController extends Controller
{
    // ─── LOAD PRODUCTS + CATEGORIES ──────────────────
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->select(
                'products.product_id',
                'products.product_name',
                'products.stock',
                'products.beginning_stock',
                'products.actual_count',
                'products.price',
                'products.low_stock_threshold',
                'products.status',
                'categories.category_id',
                'categories.category_name',
                'categories.zone',
                'categories.category_type',
                'categories.is_stock_tracked'
            )
            ->orderBy('categories.zone')
            ->orderBy('categories.category_name')
            ->orderBy('products.product_name')
            ->get();

        $categories       = DB::table('categories')->orderBy('zone')->orderBy('category_name')->get();
        $categoriesByZone = $categories->groupBy('zone');
        $zones            = $categories->pluck('zone')->unique()->values();

        // SOLD: total na nabenta ngayong period (hindi pa na-cut-off, hindi voided)
        $sold = DB::table('transaction_items as ti')
            ->join('sales_transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
            ->whereNull('t.cutoff_id')
            ->where('t.transaction_status', '!=', 'Voided')
            ->whereNotNull('ti.product_id')
            ->groupBy('ti.product_id')
            ->select('ti.product_id', DB::raw('SUM(ti.quantity) as sold'))
            ->pluck('sold', 'product_id');

        return view('inventory.index', compact('products', 'categoriesByZone', 'zones', 'sold'));
    }

    // ─── ADD CATEGORY (dynamic — para di na kailangan i-edit ang Blade) ───
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100',
            'zone'          => 'required|string|max:50',
            'category_type' => 'required|in:ride,service,merchandise',
        ]);

        $exists = DB::table('categories')
            ->whereRaw('LOWER(category_name) = ?', [strtolower(trim($request->category_name))])
            ->where('zone', $request->zone)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'That category already exists in this zone.'
            ]);
        }

        $id = DB::table('categories')->insertGetId([
            'category_name'   => trim($request->category_name),
            'zone'            => trim($request->zone),
            'category_type'   => $request->category_type,
            'is_stock_tracked'=> $request->boolean('is_stock_tracked') ? 1 : 0,
        ]);

        $category = DB::table('categories')->where('category_id', $id)->first();

        // LOG: bagong category
        $this->audit(
            'category_created',
            "Added category #{$id}: {$category->category_name} ({$category->zone})",
            null,
            [
                'category_id'      => $id,
                'category_name'    => $category->category_name,
                'zone'             => $category->zone,
                'category_type'    => $category->category_type,
                'is_stock_tracked' => (int) $category->is_stock_tracked,
            ]
        );

        return response()->json([
            'status'   => 'success',
            'message'  => 'Category added!',
            'category' => $category
        ]);
    }

    // ─── EXPORT PRODUCTS (XLSX) ───────────────────────
    public function export()
    {
        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->select(
                'products.product_name',
                'categories.zone',
                'categories.category_name',
                'categories.is_stock_tracked',
                'products.stock',
                'products.price',
                'products.low_stock_threshold',
                'products.status'
            )
            ->orderBy('categories.zone')
            ->orderBy('categories.category_name')
            ->orderBy('products.product_name')
            ->get();

        // LOG: export ng inventory report
        $this->audit(
            'inventory_exported',
            'Exported inventory report (XLSX), ' . $products->count() . ' product(s)',
            null,
            null,
            'info',
            ['rows' => $products->count()]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory');

        $sheet->setCellValue('A1', 'Pistavora Amusement Park — Inventory Report');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D63E63');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->setCellValue('A2', 'Generated: ' . date('F d, Y h:i A') . ' | Total Products: ' . $products->count());
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('635C72');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['Zone', 'Category', 'Product Name', 'Stock', 'Price (PHP)', 'Status'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        $sheet->getStyle('A4:F4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A1523');
        $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        foreach ($products as $p) {
            $isNoStock    = !$p->is_stock_tracked;
            $threshold    = intval($p->low_stock_threshold ?? 20);
            $stockDisplay = $isNoStock ? 'N/A' : intval($p->stock);
            $isLow        = !$isNoStock && intval($p->stock) <= $threshold;
            $status       = $isNoStock ? 'N/A' : ($isLow ? 'Low Stock' : 'In Stock');

            $sheet->setCellValue('A' . $row, $p->zone);
            $sheet->setCellValue('B' . $row, $p->category_name);
            $sheet->setCellValue('C' . $row, $p->product_name);
            $sheet->setCellValue('D' . $row, $stockDisplay);
            $sheet->setCellValue('E' . $row, number_format(floatval($p->price ?? 0), 2));
            $sheet->setCellValue('F' . $row, $status);

            $statusCell = 'F' . $row;
            if ($status === 'Low Stock') {
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('DC2626');
            } elseif ($status === 'In Stock') {
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E4F8F4');
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('1FAE9E');
            }

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2F6');
            }

            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A4:F' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('EEE8F0');

        foreach (['A' => 16, 'B' => 16, 'C' => 30, 'D' => 12, 'E' => 14, 'F' => 14] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        $filename = 'Pistavora_Inventory_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT PRODUCTS (CSV) ────────────────────────
    // Expected columns: product_name, zone, category_name, stock, price
    public function import(Request $request)
    {
        if (!$request->hasFile('import_file')) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded.']);
        }

        $path   = $request->file('import_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Unable to read the uploaded file.']);
        }

        $header = array_map(function ($h) { return strtolower(trim($h)); }, fgetcsv($handle));

        // Cache categories (zone+name -> category row) para hindi paulit-ulit mag-query sa loop
        $categories = DB::table('categories')->get()->keyBy(function ($c) {
            return strtolower($c->zone) . '|' . strtolower($c->category_name);
        });

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = array_combine($header, $row);

            $name         = $rowData['product_name']  ?? null;
            $categoryName = $rowData['category_name'] ?? null;
            $zone         = $rowData['zone']           ?? null;

            if (!$name || !$categoryName || !$zone) { $skipped++; continue; }

            $key      = strtolower($zone) . '|' . strtolower($categoryName);
            $category = $categories->get($key);

            if (!$category) { $skipped++; continue; } // walang match na zone+category, skip

            $stock = $category->is_stock_tracked ? intval($rowData['stock'] ?? 0) : 0;

            DB::table('products')->insert([
                'product_name'    => $name,
                'category_id'     => $category->category_id,
                'category_name'   => $category->category_name, // backup/legacy column
                'stock'           => $stock,
                'beginning_stock' => $stock, // simula ng period = initial stock
                'price'           => floatval($rowData['price'] ?? 0),
                'status'          => 'active',
            ]);

            $imported++;
        }

        fclose($handle);

        // LOG: bulk import (isang linya lang para sa buong file, hindi bawat row)
        $this->audit(
            'products_imported',
            "Imported products from CSV: {$imported} added, {$skipped} skipped",
            null,
            null,
            'info',
            ['imported' => $imported, 'skipped' => $skipped]
        );

        $message = "Imported {$imported} product(s) successfully!";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped (zone/category not found).";
        }

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    // ─── SHARED NOTIFICATION HELPER ────────────────────────
    // Ginagamit ito ng add/edit/delete/restock/low-stock check.
    // Naka-try/catch para hindi ito makasira sa pangunahing action
    // (add/edit/delete/restock) kahit mag-fail ang broadcast.
    // $message ay pwedeng multi-line (\n) — ang popup sa frontend na
    // ang bahalang mag-preserve ng line breaks (white-space: pre-line).
    private function notify($title, $message, $url = null)
    {
        try {
            $notif = \App\Models\Notification::create([
                'type'    => 'inventory',
                'title'   => $title,
                'message' => $message,
                'url'     => $url ?: url('/inventory'),
            ]);
            broadcast(new \App\Events\NewSystemNotification($notif));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification/broadcast failed: ' . $e->getMessage());
        }
    }

    // ─── SYSTEM LOG HELPER ─────────────────────────────────
    // Isang lugar lang ang tawag sa SystemLogger::record().
    // - Session ang gamit ng app (hindi Laravel Auth), kaya manual na
    //   ipinapasa ang user_id at email para hindi mag-null ang "sino".
    // - Ang $old at $new ay napupunta sa context['old'] / context['new'].
    // - Kapag ang event ay nagtatapos sa "_deleted", tinatawag din ang
    //   checkBulkDelete() para ma-detect ang sunod-sunod na delete.
    // - Naka-try/catch: hindi masisira ang main action kapag nag-fail ang log.
    private function audit(
        string $eventType,
        string $description,
        ?array $old = null,
        ?array $new = null,
        string $severity = 'info',
        array $extra = []
    ): void {
        try {
            $sessionId = session('user_id');
            $userId    = is_numeric($sessionId) ? (int) $sessionId : null;
            $email     = session('email'); // palitan kung iba ang session key ng email mo

            $context = $extra;
            if ($old !== null) { $context['old'] = $old; }
            if ($new !== null) { $context['new'] = $new; }

            SystemLogger::record(
                $eventType,
                $description,
                $severity,
                $context,
                false,
                $userId,
                $email,
                'inventory'
            );

            if (str_ends_with($eventType, '_deleted')) {
                SystemLogger::checkBulkDelete($userId, 'inventory');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SystemLogger failed (Inventory): ' . $e->getMessage());
        }
    }

    // ─── ADD PRODUCT ──────────────────────────────────
    public function store(Request $request)
    {
        $category = DB::table('categories')->where('category_id', $request->category_id)->first();

        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Please select a valid category.']);
        }

        $stock = $category->is_stock_tracked
            ? (($request->stock !== '' && $request->stock !== null) ? intval($request->stock) : 0)
            : 0;
        $price = floatval($request->price);

        $id = DB::table('products')->insertGetId([
            'product_name'    => $request->name,
            'category_id'     => $category->category_id,
            'category_name'   => $category->category_name, // legacy/backup, laging naka-sync
            'stock'           => $stock,
            'beginning_stock' => $stock, // bagong product: initial stock = beginning
            'price'           => $price,
            'status'          => 'active',
        ]);

        // LOG: bagong product
        $this->audit(
            'product_created',
            "Added product #{$id}: {$request->name}",
            null,
            [
                'product_id' => $id,
                'name'       => $request->name,
                'category'   => "{$category->category_name} ({$category->zone})",
                'stock'      => $category->is_stock_tracked ? $stock : null,
                'price'      => $price,
            ]
        );

        // Full details ng bagong idinagdag na product
        $details = [
            "Category: {$category->category_name} ({$category->zone})",
            "Stock: " . ($category->is_stock_tracked ? $stock : 'Not tracked'),
            "Price: ₱" . number_format($price, 2),
        ];
        $this->notify('Product Added', "{$request->name} was added.\n" . implode("\n", $details));
        $this->checkStockAlert($id);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Product added successfully!',
            'id'       => $id,
            'category' => [
                'id'               => $category->category_id,
                'name'             => $category->category_name,
                'zone'             => $category->zone,
                'is_stock_tracked' => (bool) $category->is_stock_tracked,
            ]
        ]);
    }

    // ─── EDIT PRODUCT ─────────────────────────────────
    public function update(Request $request)
    {
        $category = DB::table('categories')->where('category_id', $request->category_id)->first();

        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Please select a valid category.']);
        }

        // Kunin ang LUMANG values BAGO i-update — ito ang gagamitin
        // natin para sa before/after na detalye ng notification.
        $old = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->where('products.product_id', $request->id)
            ->select('products.*', 'categories.category_name as old_category_name', 'categories.zone as old_zone')
            ->first();

        $newStock = $category->is_stock_tracked ? intval($request->stock) : 0;
        $newPrice = floatval($request->price);

        $data = [
            'product_name'  => $request->name,
            'category_id'   => $category->category_id,
            'category_name' => $category->category_name,
            'price'         => $newPrice,
            'stock'         => $newStock,
        ];

        // Kapag manual na binago ang stock sa edit, sabay ding gumalaw ang
        // Beginning (same difference) para tama pa rin ang Beginning − Sold = Stock.
        if ($old && $category->is_stock_tracked) {
            $delta = $newStock - (int) $old->stock;
            if ($delta !== 0) {
                $data['beginning_stock'] = (int) ($old->beginning_stock ?? $old->stock) + $delta;
            }
        }

        DB::table('products')->where('product_id', $request->id)->update($data);

        // Ihambing ang luma vs bago, isama lang sa message yung mga
        // field na TALAGANG nagbago (para hindi puno ng walang silbing
        // "Price: 65.00 → 65.00" kapag hindi naman iyon binago).
        $changes = [];
        if ($old) {
            if ((string) $old->product_name !== (string) $request->name) {
                $changes[] = "Name: {$old->product_name} → {$request->name}";
            }
            if ((int) $old->category_id !== (int) $category->category_id) {
                $changes[] = "Category: {$old->old_category_name} → {$category->category_name}";
            }
            if (number_format((float) $old->price, 2) !== number_format($newPrice, 2)) {
                $changes[] = "Price: ₱" . number_format((float) $old->price, 2) . " → ₱" . number_format($newPrice, 2);
            }
            if ($category->is_stock_tracked && (int) $old->stock !== $newStock) {
                $changes[] = "Stock: {$old->stock} → {$newStock}";
            }
        }

        // LOG: hiwalay na entry para sa presyo, stock, at iba pang field,
        // para madaling i-filter sa log page.
        if ($old) {
            $ref = "#{$request->id} {$old->product_name}";

            if (number_format((float) $old->price, 2) !== number_format($newPrice, 2)) {
                $this->audit(
                    'price_changed',
                    "Changed price of product {$ref}",
                    ['price' => (float) $old->price],
                    ['price' => $newPrice],
                    'warning'
                );
            }

            // Stock log lang kung tracked ang category (kung hindi, laging 0 ang newStock)
            if ($category->is_stock_tracked && (int) $old->stock !== $newStock) {
                $this->audit(
                    'stock_adjusted',
                    "Adjusted stock of product {$ref} (via edit)",
                    ['stock' => (int) $old->stock],
                    ['stock' => $newStock]
                );
            }

            $otherOld = [];
            $otherNew = [];
            if ((string) $old->product_name !== (string) $request->name) {
                $otherOld['name'] = $old->product_name;
                $otherNew['name'] = $request->name;
            }
            if ((int) $old->category_id !== (int) $category->category_id) {
                $otherOld['category'] = "{$old->old_category_name} ({$old->old_zone})";
                $otherNew['category'] = "{$category->category_name} ({$category->zone})";
            }
            if ($otherNew) {
                $this->audit('product_updated', "Edited product {$ref}", $otherOld, $otherNew);
            }
        }

        $message = $changes
            ? "{$request->name} was updated:\n" . implode("\n", $changes)
            : "{$request->name} was updated (no field values changed).";

        $this->notify('Product Updated', $message);
        $this->checkStockAlert($request->id);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Product updated!',
            'category' => [
                'id'               => $category->category_id,
                'name'             => $category->category_name,
                'zone'             => $category->zone,
                'is_stock_tracked' => (bool) $category->is_stock_tracked,
            ]
        ]);
    }

    // ─── DELETE PRODUCT ───────────────────────────────
    public function destroy(Request $request)
    {
        // Kunin ang buong record BAGO i-delete — kailangan ito para
        // may laman pa ang notification pagkatapos matanggal sa DB.
        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->where('products.product_id', $request->id)
            ->select('products.*', 'categories.category_name', 'categories.zone', 'categories.is_stock_tracked')
            ->first();

        DB::table('products')->where('product_id', $request->id)->delete();

        if ($product) {
            // LOG: delete ng product (huling laman ng record ang nilalagay bilang "old")
            $this->audit(
                'product_deleted',
                "Deleted product #{$product->product_id}: {$product->product_name}",
                [
                    'product_id' => $product->product_id,
                    'name'       => $product->product_name,
                    'category'   => "{$product->category_name} ({$product->zone})",
                    'stock'      => $product->is_stock_tracked ? (int) $product->stock : null,
                    'price'      => (float) $product->price,
                ],
                null,
                'warning'
            );

            $details = [
                "Category: {$product->category_name} ({$product->zone})",
                "Stock at deletion: " . ($product->is_stock_tracked ? $product->stock : 'Not tracked'),
                "Price: ₱" . number_format((float) $product->price, 2),
            ];
            $this->notify('Product Deleted', "{$product->product_name} was removed from inventory.\n" . implode("\n", $details));
        }

        return response()->json(['status' => 'success', 'message' => 'Product deleted!']);
    }

    // ─── RESTOCK PRODUCT ──────────────────────────────
    public function restock(Request $request)
    {
        $qty = intval($request->qty);

        if ($qty <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Quantity must be greater than 0.']);
        }

        $before = DB::table('products')->where('product_id', $request->id)->first();
        $productName = $before->product_name ?? 'Product';
        $oldStock = intval($before->stock ?? 0);

        // Ang restock ay idinadagdag sa stock AT sa beginning, para
        // tama pa rin ang Beginning − Sold = Stock.
        DB::table('products')->where('product_id', $request->id)->update([
            'stock'           => DB::raw('stock + ' . $qty),
            'beginning_stock' => DB::raw('COALESCE(beginning_stock, stock) + ' . $qty),
        ]);

        $newStock = DB::table('products')->where('product_id', $request->id)->value('stock');

        // LOG: stock adjustment (dating bilang → bagong bilang)
        if ($before) {
            $this->audit(
                'stock_adjusted',
                "Restocked product #{$request->id} {$productName} (+{$qty})",
                ['stock' => $oldStock],
                ['stock' => (int) $newStock],
                'info',
                ['added' => $qty]
            );
        }

        $details = [
            "Added: +{$qty}",
            "Before: {$oldStock}",
            "After: {$newStock}",
        ];
        $this->notify('Product Restocked', "{$productName} was restocked.\n" . implode("\n", $details));
        $this->checkStockAlert($request->id);

        return response()->json([
            'status'    => 'success',
            'message'   => "Stock updated! Added {$qty} units.",
            'new_stock' => $newStock
        ]);
    }

    // ─── SAVE PHYSICAL COUNT ──────────────────────────
    // Actual Count = totoong bilang na tinype ng staff.
    // Variance = Actual − System Stock (negative = Short, positive = Over).
    public function saveCount(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated.'], 401);
        }

        $request->validate([
            'id'           => 'required|integer',
            'actual_count' => 'required|integer|min:0',
        ]);

        $p = DB::table('products')->where('product_id', $request->id)->first();
        if (!$p) {
            return response()->json(['status' => 'error', 'message' => 'Product not found.']);
        }

        $actual   = (int) $request->actual_count;
        $variance = $actual - (int) $p->stock;

        DB::table('products')->where('product_id', $request->id)->update([
            'actual_count' => $actual,
            'counted_at'   => now(),
        ]);

        // LOG: physical count (warning kapag may short/over)
        $this->audit(
            'stock_counted',
            "Physical count for #{$p->product_id} {$p->product_name}: "
                . ($variance === 0 ? 'balanced' : ($variance < 0 ? 'SHORT ' : 'OVER ') . abs($variance)),
            ['stock' => (int) $p->stock],
            ['actual_count' => $actual],
            $variance === 0 ? 'info' : 'warning',
            ['variance' => $variance]
        );

        return response()->json([
            'status'   => 'success',
            'variance' => $variance,
            'label'    => $variance === 0 ? 'Balanced' : ($variance < 0 ? 'Short' : 'Over'),
        ]);
    }

    // ─── STOCK ALERT CHECK (Low Stock / Out of Stock) ─────
    private function checkStockAlert($productId)
    {
        $product = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->where('products.product_id', $productId)
            ->select('products.*', 'categories.is_stock_tracked')
            ->first();

        if (!$product || !$product->is_stock_tracked) {
            return;
        }

        $stock     = intval($product->stock);
        $threshold = intval($product->low_stock_threshold ?? 20);

        if ($stock <= 0) {
            $this->notify('Out of Stock', "{$product->product_name} is out of stock.\nCurrent stock: 0\nThreshold: {$threshold}");
        } elseif ($stock <= $threshold) {
            $this->notify('Low Stock Alert', "{$product->product_name} is low on stock.\nCurrent stock: {$stock}\nThreshold: {$threshold}");
        }
    }
}