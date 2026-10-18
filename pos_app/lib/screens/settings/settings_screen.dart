import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../providers/auth_provider.dart';
import '../../providers/printer_provider.dart';
import '../auth/login_screen.dart';
import '../printer/printer_settings_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  late TextEditingController _emailController;
  late TextEditingController _addressController;
  late TextEditingController _taxController;
  late TextEditingController _headerController;
  late TextEditingController _footerController;
  late TextEditingController _currencyController;

  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final shop = context.read<AuthProvider>().shop ?? {};

    _nameController = TextEditingController(text: shop['name'] ?? 'NesCafe Solapur');
    _phoneController = TextEditingController(text: shop['phone'] ?? '+91 98220 12345');
    _emailController = TextEditingController(text: shop['email'] ?? 'store@pospro.app');
    _addressController = TextEditingController(text: shop['address'] ?? 'Station Road, Solapur, Maharashtra 413001');
    _taxController = TextEditingController(text: shop['tax_number'] ?? '27AAECN1234F1Z8');
    _headerController = TextEditingController(text: shop['receipt_header'] ?? 'Fresh Brews & Bites');
    _footerController = TextEditingController(text: shop['receipt_footer'] ?? 'Thank you for visiting NesCafe!');
    _currencyController = TextEditingController(text: shop['currency_symbol'] ?? '₹');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _addressController.dispose();
    _taxController.dispose();
    _headerController.dispose();
    _footerController.dispose();
    _currencyController.dispose();
    super.dispose();
  }

  Future<void> _saveSettings() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);
    final auth = context.read<AuthProvider>();

    try {
      final payload = {
        'name': _nameController.text.trim(),
        'phone': _phoneController.text.trim(),
        'email': _emailController.text.trim(),
        'address': _addressController.text.trim(),
        'tax_number': _taxController.text.trim(),
        'receipt_header': _headerController.text.trim(),
        'receipt_footer': _footerController.text.trim(),
        'currency_symbol': _currencyController.text.trim().isNotEmpty ? _currencyController.text.trim() : '₹',
      };

      final res = await ApiClient().dio.post('/pos/settings', data: payload);
      if (res.data['success'] == true && mounted) {
        auth.updateShop(Map<String, dynamic>.from(res.data['data']));
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Store settings updated successfully!'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to save settings: $e'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }

    setState(() => _isSaving = false);
  }


  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final printer = context.watch<PrinterProvider>();
    final user = auth.user ?? {};
    final isOwnerOrAdmin = auth.isOwnerOrAdmin;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Store & System Settings', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // User Profile & Role Card
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 28,
                      backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                      child: const Icon(Icons.person_rounded, size: 32, color: AppColors.primary),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            user['name'] ?? 'Store User',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
                          ),
                          Text(
                            user['email'] ?? '',
                            style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                            decoration: BoxDecoration(
                              color: isOwnerOrAdmin ? Colors.amber.shade100 : Colors.blue.shade100,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              auth.roleDisplayName.toUpperCase(),
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isOwnerOrAdmin ? Colors.amber.shade900 : Colors.blue.shade900,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Thermal Printer Quick Status Card
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Icon(
                              printer.isConnected ? Icons.print_rounded : Icons.print_disabled_rounded,
                              color: printer.isConnected ? AppColors.success : AppColors.warning,
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'Thermal Printer',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: printer.isConnected ? Colors.green.shade50 : Colors.orange.shade50,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            printer.isConnected ? 'Connected' : 'Offline',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: printer.isConnected ? Colors.green.shade700 : Colors.orange.shade800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      printer.isConnected
                          ? 'Active Printer: ${printer.selectedDevice?.name ?? 'Bluetooth Thermal'} (${printer.paperSizeLabel})'
                          : 'No Bluetooth printer currently connected.',
                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(builder: (_) => const PrinterSettingsScreen()),
                              );
                            },
                            icon: const Icon(Icons.settings_bluetooth_rounded, size: 18),
                            label: const Text('Manage Printer'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppColors.primary,
                              side: const BorderSide(color: AppColors.border),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ),
                        if (printer.isConnected) ...[
                          const SizedBox(width: 10),
                          ElevatedButton.icon(
                            onPressed: () async {
                              final ok = await printer.printTestSlip();
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(ok ? 'Test receipt printed!' : 'Test print failed'),
                                    backgroundColor: ok ? AppColors.success : AppColors.danger,
                                  ),
                                );
                              }
                            },
                            icon: const Icon(Icons.receipt_rounded, size: 18),
                            label: const Text('Test Print'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Store Profile Form
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.storefront_rounded, color: AppColors.primary),
                              SizedBox(width: 8),
                              Text('Store Profile & Billing', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            ],
                          ),
                          if (!isOwnerOrAdmin)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: BorderRadius.circular(6)),
                              child: const Text('Read-Only (Owner Only)', style: TextStyle(fontSize: 10, color: Colors.grey)),
                            ),
                        ],
                      ),
                      const Divider(height: 20),

                      // Store Name
                      const Text('Store / Cafe Name *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _nameController,
                        enabled: isOwnerOrAdmin,
                        decoration: InputDecoration(
                          hintText: 'e.g. NesCafe Solapur',
                          filled: true,
                          fillColor: AppColors.background,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Store name required' : null,
                      ),
                      const SizedBox(height: 14),

                      // Address
                      const Text('Store Address', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _addressController,
                        enabled: isOwnerOrAdmin,
                        maxLines: 2,
                        decoration: InputDecoration(
                          hintText: 'Station Road, Solapur, Maharashtra 413001',
                          filled: true,
                          fillColor: AppColors.background,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Phone & Tax Number
                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Phone Number', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                const SizedBox(height: 6),
                                TextFormField(
                                  controller: _phoneController,
                                  enabled: isOwnerOrAdmin,
                                  decoration: InputDecoration(
                                    hintText: '+91 98220 12345',
                                    filled: true,
                                    fillColor: AppColors.background,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('GSTIN / Tax No.', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                const SizedBox(height: 6),
                                TextFormField(
                                  controller: _taxController,
                                  enabled: isOwnerOrAdmin,
                                  decoration: InputDecoration(
                                    hintText: '27AAECN1234F1Z8',
                                    filled: true,
                                    fillColor: AppColors.background,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Receipt Header & Footer
                      const Text('Thermal Receipt Header Note', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _headerController,
                        enabled: isOwnerOrAdmin,
                        decoration: InputDecoration(
                          hintText: 'e.g. Fresh Brews & Bites',
                          filled: true,
                          fillColor: AppColors.background,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                      const SizedBox(height: 14),

                      const Text('Thermal Receipt Footer Thank-You Note', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _footerController,
                        enabled: isOwnerOrAdmin,
                        decoration: InputDecoration(
                          hintText: 'Thank you for visiting NesCafe! Free Wi-Fi: NesCafe_Guest',
                          filled: true,
                          fillColor: AppColors.background,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                        ),
                      ),
                      const SizedBox(height: 20),

                      if (isOwnerOrAdmin)
                        ElevatedButton.icon(
                          onPressed: _isSaving ? null : _saveSettings,
                          icon: const Icon(Icons.save_rounded, size: 20),
                          label: _isSaving
                              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : const Text('Save Store Settings', style: TextStyle(fontWeight: FontWeight.bold)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: Colors.white,
                            minimumSize: const Size.fromHeight(50),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),

            const SizedBox(height: 20),

            // Logout Button
            OutlinedButton.icon(
              onPressed: () async {
                await auth.logout();
                if (context.mounted) {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                }
              },
              icon: const Icon(Icons.logout_rounded, color: AppColors.danger),
              label: const Text('Sign Out from POS Terminal', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(50),
                side: const BorderSide(color: AppColors.danger),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }
}
