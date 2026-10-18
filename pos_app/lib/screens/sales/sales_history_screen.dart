import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../models/sale_order_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/printer_provider.dart';
import '../pos/receipt_screen.dart';
import '../printer/printer_settings_screen.dart';

class SalesHistoryScreen extends StatefulWidget {
  const SalesHistoryScreen({super.key});

  @override
  State<SalesHistoryScreen> createState() => _SalesHistoryScreenState();
}

class _SalesHistoryScreenState extends State<SalesHistoryScreen> {
  List<SaleOrderModel> _sales = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchSales();
  }

  Future<void> _fetchSales() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiClient().dio.get('/pos/sales');
      if (res.data['success'] == true) {
        final list = (res.data['data']['data'] as List)
            .map((s) => SaleOrderModel.fromJson(s))
            .toList();
        setState(() => _sales = list);
      }
    } catch (e) {
      debugPrint('Error fetching sales: $e');
    }
    setState(() => _isLoading = false);
  }

  void _reprintReceipt(SaleOrderModel order) async {
    final auth = context.read<AuthProvider>();
    final printer = context.read<PrinterProvider>();
    final shop = auth.shop ?? {};

    // Build thermal payload from the saved sale order
    final thermalPayload = {
      'store': {
        'name': shop['name'] ?? 'NesCafe Solapur',
        'address': shop['address'] ?? 'Station Road, Solapur, Maharashtra 413001',
        'phone': shop['phone'] ?? '+91 98220 12345',
        'tax_number': shop['tax_number'] ?? '27AAECN1234F1Z8',
        'header': shop['receipt_header'] ?? 'Fresh Brews & Bites',
        'footer': shop['receipt_footer'] ?? 'Thank you for visiting NesCafe!',
        'currency_symbol': shop['currency_symbol'] ?? '₹',
      },
      'invoice': {
        'invoice_number': order.invoiceNumber,
        'date': order.saleDate,
        'cashier': order.cashierName,
        'customer': order.customerName,
        'payment_method': order.paymentMethod,
        'subtotal': order.raw['subtotal'] ?? order.totalAmount,
        'discount': order.raw['discount_amount'] ?? 0,
        'tax': order.raw['tax_amount'] ?? 0,
        'grand_total': order.totalAmount,
        'paid_amount': order.paidAmount,
        'due_amount': order.dueAmount,
        'items': (order.raw['items'] as List? ?? []).map((i) {
          return {
            'name': i['product_name'] ?? i['product']?['name'] ?? 'Item',
            'quantity': i['quantity'] ?? '1',
            'unit_price': i['unit_price'] ?? '0',
            'subtotal': i['subtotal'] ?? '0',
          };
        }).toList(),
      },
    };

    if (!printer.isConnected) {
      // Navigate to receipt preview if printer is not currently connected
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => ReceiptScreen(thermalPayload: thermalPayload),
        ),
      );
      return;
    }

    final success = await printer.printSaleReceipt(
      store: thermalPayload['store'] as Map<String, dynamic>,
      invoice: thermalPayload['invoice'] as Map<String, dynamic>,
    );

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(success ? 'Receipt reprinted on thermal printer!' : 'Reprint failed. Check printer connection.'),
          backgroundColor: success ? AppColors.success : AppColors.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final printer = context.watch<PrinterProvider>();
    final sym = auth.currencySymbol;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Sales & Invoices', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(
              printer.isConnected ? Icons.print_rounded : Icons.print_disabled_rounded,
              color: printer.isConnected ? Colors.greenAccent : Colors.white70,
            ),
            tooltip: 'Printer Settings',
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const PrinterSettingsScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh Sales',
            onPressed: _fetchSales,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _sales.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.receipt_long_rounded, size: 54, color: Colors.grey.shade400),
                      const SizedBox(height: 12),
                      const Text('No sales records yet.', style: TextStyle(color: AppColors.textSecondary)),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _sales.length,
                  itemBuilder: (context, index) {
                    final order = _sales[index];

                    return Card(
                      elevation: 1.5,
                      margin: const EdgeInsets.only(bottom: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      color: Colors.white,
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Header Row: Invoice # & Payment Badge
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    const Icon(Icons.receipt_rounded, size: 18, color: AppColors.primary),
                                    const SizedBox(width: 8),
                                    Text(
                                      order.invoiceNumber,
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    order.paymentMethod,
                                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.primary),
                                  ),
                                ),
                              ],
                            ),
                            const Divider(height: 18),

                            // Customer & Cashier info
                            Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Customer: ${order.customerName}',
                                        style: const TextStyle(fontSize: 13, color: AppColors.textPrimary),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Cashier: ${order.cashierName} • ${order.itemsCount} Items',
                                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                      ),
                                    ],
                                  ),
                                ),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    Text(
                                      '$sym${order.totalAmount.toStringAsFixed(2)}',
                                      style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.primary),
                                    ),
                                    Text(
                                      order.saleDate,
                                      style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),

                            // Reprint Button
                            Row(
                              mainAxisAlignment: MainAxisAlignment.end,
                              children: [
                                OutlinedButton.icon(
                                  onPressed: () => _reprintReceipt(order),
                                  icon: const Icon(Icons.print_rounded, size: 16),
                                  label: const Text('Reprint Receipt'),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: AppColors.primary,
                                    side: const BorderSide(color: AppColors.border),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
