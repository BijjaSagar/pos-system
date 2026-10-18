class CustomerModel {
  final int id;
  final String name;
  final String? phone;
  final String? email;
  final String tier; // retailer, dealer, wholesaler
  final double currentBalance;

  CustomerModel({
    required this.id,
    required this.name,
    this.phone,
    this.email,
    required this.tier,
    required this.currentBalance,
  });

  factory CustomerModel.fromJson(Map<String, dynamic> json) {
    return CustomerModel(
      id: json['id'],
      name: json['name'] ?? '',
      phone: json['phone'],
      email: json['email'],
      tier: json['customer_tier'] ?? 'retailer',
      currentBalance: double.tryParse(json['current_balance']?.toString() ?? '0') ?? 0.0,
    );
  }
}
