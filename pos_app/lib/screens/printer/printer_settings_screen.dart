import 'package:esc_pos_utils_plus/esc_pos_utils_plus.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/printer_provider.dart';

class PrinterSettingsScreen extends StatefulWidget {
  const PrinterSettingsScreen({super.key});

  @override
  State<PrinterSettingsScreen> createState() => _PrinterSettingsScreenState();
}

class _PrinterSettingsScreenState extends State<PrinterSettingsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PrinterProvider>().scanDevices();
    });
  }

  @override
  Widget build(BuildContext context) {
    final printer = context.watch<PrinterProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Thermal Printer Settings'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Rescan Bluetooth Devices',
            onPressed: () => printer.scanDevices(),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Paper Size Configuration Card
          Card(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            elevation: 2,
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.print_rounded, color: AppColors.primary),
                      SizedBox(width: 8),
                      Text(
                        'Paper Width Specification',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Select the thermal roll width loaded in your printer:',
                    style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ChoiceChip(
                          label: const Center(child: Text('58mm (2-Inch / 32 Col)')),
                          selected: printer.paperSize == PaperSize.mm58,
                          selectedColor: AppColors.primary.withValues(alpha: 0.2),
                          onSelected: (_) => printer.setPaperSize(PaperSize.mm58),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ChoiceChip(
                          label: const Center(child: Text('80mm (3-Inch / 48 Col)')),
                          selected: printer.paperSize == PaperSize.mm80,
                          selectedColor: AppColors.primary.withValues(alpha: 0.2),
                          onSelected: (_) => printer.setPaperSize(PaperSize.mm80),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Printer Connection State
          Card(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            elevation: 2,
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: printer.isConnected ? Colors.green.shade50 : Colors.red.shade50,
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          printer.isConnected ? Icons.bluetooth_connected_rounded : Icons.bluetooth_disabled_rounded,
                          color: printer.isConnected ? Colors.green : Colors.red,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              printer.isConnected ? 'Connected to Printer' : 'No Printer Connected',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                            ),
                            Text(
                              printer.selectedDevice != null
                                  ? '${printer.selectedDevice!.name} (${printer.selectedDevice!.macAdress})'
                                  : 'Select a paired Bluetooth device below',
                              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                      if (printer.isConnected)
                        OutlinedButton(
                          onPressed: () => printer.disconnect(),
                          style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                          child: const Text('Disconnect'),
                        ),
                    ],
                  ),
                  const Divider(height: 24),
                  ElevatedButton.icon(
                    onPressed: printer.isConnected
                        ? () async {
                            final ok = await printer.printTestSlip();
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(ok ? 'Test voucher printed!' : 'Failed to print test slip'),
                                ),
                              );
                            }
                          }
                        : null,
                    icon: const Icon(Icons.receipt_long_rounded),
                    label: const Text('Print Test Receipt'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      minimumSize: const Size.fromHeight(44),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 24),

          const Text(
            'Paired Bluetooth Devices',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
          ),
          const SizedBox(height: 8),

          if (printer.devices.isEmpty)
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: const Padding(
                padding: EdgeInsets.all(24),
                child: Center(
                  child: Text(
                    'No paired Bluetooth printers found.\nPlease pair your thermal printer in Android Bluetooth Settings first.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: AppColors.textSecondary),
                  ),
                ),
              ),
            )
          else
            ...printer.devices.map((device) {
              final isThisDeviceConnected = printer.isConnected && printer.selectedDevice?.macAdress == device.macAdress;

              return Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  leading: const Icon(Icons.print, color: AppColors.primary),
                  title: Text(device.name.isEmpty ? 'Unnamed Printer' : device.name, style: const TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: Text(device.macAdress),
                  trailing: isThisDeviceConnected
                      ? const Chip(
                          label: Text('Active', style: TextStyle(color: Colors.white, fontSize: 12)),
                          backgroundColor: Colors.green,
                        )
                      : ElevatedButton(
                          onPressed: printer.isConnecting
                              ? null
                              : () async {
                                  final ok = await printer.connect(device);
                                  if (context.mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(ok ? 'Connected to ${device.name}' : 'Connection failed'),
                                      ),
                                    );
                                  }
                                },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: Colors.white,
                          ),
                          child: const Text('Connect'),
                        ),
                ),
              );
            }),
        ],
      ),
    );
  }
}
