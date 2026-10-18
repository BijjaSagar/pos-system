class SaleOrderModel {
  final int id;
  final String invoiceNumber;
  final String saleDate;
  final double totalAmount;
  final double paidAmount;
  final double dueAmount;
  final String paymentMethod;
  final String customerName;
  final String cashierName;
  final int itemsCount;
  final Map<String, dynamic> raw;

  SaleOrderModel({
    required this.id,
    required this.invoiceNumber,
    required this.saleDate,
    required this.totalAmount,
    required this.paidAmount,
    required this.dueAmount,
    required this.paymentMethod,
    required this.customerName,
    required this.cashierName,
    required this.itemsCount,
    required this.raw,
  });

  factory SaleOrderModel.fromJson(Map<String, dynamic> json) {
    final customer = json['customer'] as Map<String, dynamic>?;
    final cashier = json['cashier'] as Map<String, dynamic>?;
    final items = json['items'] as List? ?? [];

    return SaleOrderModel(
      id: json['id'] ?? 0,
      invoiceNumber: json['invoice_number'] ?? 'INV-${json['id']}',
      saleDate: json['sale_date'] ?? json['created_at'] ?? '',
      totalAmount: double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      paidAmount: double.tryParse(json['paid_amount']?.toString() ?? '0') ?? 0.0,
      dueAmount: double.tryParse(json['due_amount']?.toString() ?? '0') ?? 0.0,
      paymentMethod: (json['payment_method'] ?? 'cash').toString().toUpperCase(),
      customerName: customer?['name'] ?? 'Walk-In Customer',
      cashierName: cashier?['name'] ?? 'Staff',
      itemsCount: items.length,
      raw: json,
    );
  }
}
