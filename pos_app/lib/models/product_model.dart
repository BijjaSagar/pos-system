class ProductModel {
  final int id;
  final String name;
  final String code;
  final double purchasePrice;
  final double retailerPrice;
  final double dealerPrice;
  final double wholesalePrice;
  final double stock;
  final double alertQuantity;
  final double taxRate;
  final String? imageUrl;
  final int? categoryId;
  final String? categoryName;
  final String? unitName;
  final String? description;
  double activePrice;

  ProductModel({
    required this.id,
    required this.name,
    required this.code,
    required this.purchasePrice,
    required this.retailerPrice,
    required this.dealerPrice,
    required this.wholesalePrice,
    required this.stock,
    required this.alertQuantity,
    required this.taxRate,
    this.imageUrl,
    this.categoryId,
    this.categoryName,
    this.unitName,
    this.description,
    required this.activePrice,
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    return ProductModel(
      id: json['id'],
      name: json['name'] ?? '',
      code: json['code'] ?? '',
      purchasePrice: double.tryParse(json['purchase_price']?.toString() ?? '0') ?? 0.0,
      retailerPrice: double.tryParse(json['retailer_price']?.toString() ?? '0') ?? 0.0,
      dealerPrice: double.tryParse(json['dealer_price']?.toString() ?? '0') ?? 0.0,
      wholesalePrice: double.tryParse(json['wholesale_price']?.toString() ?? '0') ?? 0.0,
      stock: double.tryParse(json['stock']?.toString() ?? '0') ?? 0.0,
      alertQuantity: double.tryParse(json['alert_quantity']?.toString() ?? '0') ?? 0.0,
      taxRate: double.tryParse(json['tax_rate']?.toString() ?? '0') ?? 0.0,
      imageUrl: json['image_url'],
      categoryId: json['category_id'] ?? json['category']?['id'],
      categoryName: json['category']?['name'],
      unitName: json['unit']?['short_name'],
      description: json['description'],
      activePrice: double.tryParse(json['active_price']?.toString() ?? json['retailer_price']?.toString() ?? '0') ?? 0.0,
    );
  }

  void updateTier(String tier) {
    if (tier == 'dealer' && dealerPrice > 0) {
      activePrice = dealerPrice;
    } else if (tier == 'wholesaler' && wholesalePrice > 0) {
      activePrice = wholesalePrice;
    } else {
      activePrice = retailerPrice;
    }
  }
}
