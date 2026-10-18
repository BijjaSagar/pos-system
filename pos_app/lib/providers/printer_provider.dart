import 'package:esc_pos_utils_plus/esc_pos_utils_plus.dart';
import 'package:flutter/material.dart';
import 'package:print_bluetooth_thermal/print_bluetooth_thermal.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/thermal_printer_service.dart';

class PrinterProvider extends ChangeNotifier {
  List<BluetoothInfo> _devices = [];
  BluetoothInfo? _selectedDevice;
  bool _isConnected = false;
  bool _isConnecting = false;
  PaperSize _paperSize = PaperSize.mm58;

  List<BluetoothInfo> get devices => _devices;
  BluetoothInfo? get selectedDevice => _selectedDevice;
  bool get isConnected => _isConnected;
  bool get isConnecting => _isConnecting;
  PaperSize get paperSize => _paperSize;
  String get paperSizeLabel => _paperSize == PaperSize.mm58 ? '58mm (2-inch)' : '80mm (3-inch)';

  PrinterProvider() {
    _init();
  }

  Future<void> _init() async {
    final prefs = await SharedPreferences.getInstance();
    final savedSize = prefs.getString('printer_paper_size') ?? 'mm58';
    _paperSize = savedSize == 'mm80' ? PaperSize.mm80 : PaperSize.mm58;

    await checkConnection();
    await scanDevices();
  }

  Future<void> scanDevices() async {
    _devices = await ThermalPrinterService.getPairedDevices();
    notifyListeners();
  }

  Future<void> checkConnection() async {
    _isConnected = await ThermalPrinterService.isConnected();
    notifyListeners();
  }

  Future<bool> connect(BluetoothInfo device) async {
    _isConnecting = true;
    notifyListeners();

    final result = await ThermalPrinterService.connectPrinter(device.macAdress);
    _isConnecting = false;
    _isConnected = result;

    if (result) {
      _selectedDevice = device;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('printer_mac', device.macAdress);
    }

    notifyListeners();
    return result;
  }

  Future<void> disconnect() async {
    await ThermalPrinterService.disconnectPrinter();
    _isConnected = false;
    _selectedDevice = null;
    notifyListeners();
  }

  Future<void> setPaperSize(PaperSize size) async {
    _paperSize = size;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('printer_paper_size', size == PaperSize.mm80 ? 'mm80' : 'mm58');
    notifyListeners();
  }

  Future<bool> printSaleReceipt({
    required Map<String, dynamic> store,
    required Map<String, dynamic> invoice,
  }) async {
    try {
      final bytes = await ThermalPrinterService.generateSaleReceiptBytes(
        store: store,
        invoice: invoice,
        paperSize: _paperSize,
      );
      return await ThermalPrinterService.sendBytesToPrinter(bytes);
    } catch (e) {
      debugPrint('Print error: $e');
      return false;
    }
  }

  Future<bool> printTestSlip() async {
    try {
      final bytes = await ThermalPrinterService.generateTestReceiptBytes(paperSize: _paperSize);
      return await ThermalPrinterService.sendBytesToPrinter(bytes);
    } catch (e) {
      debugPrint('Test print error: $e');
      return false;
    }
  }
}
