import 'package:esc_pos_utils_plus/esc_pos_utils_plus.dart';
import 'package:print_bluetooth_thermal/print_bluetooth_thermal.dart';

class ThermalPrinterService {
  /// Fetch list of bonded/paired Bluetooth devices
  static Future<List<BluetoothInfo>> getPairedDevices() async {
    try {
      final List<BluetoothInfo> list = await PrintBluetoothThermal.pairedBluetooths;
      return list;
    } catch (e) {
      return [];
    }
  }

  /// Connect to Bluetooth Thermal Printer via MAC Address
  static Future<bool> connectPrinter(String macAddress) async {
    try {
      final bool result = await PrintBluetoothThermal.connect(macPrinterAddress: macAddress);
      return result;
    } catch (e) {
      return false;
    }
  }

  /// Disconnect printer
  static Future<bool> disconnectPrinter() async {
    try {
      final bool result = await PrintBluetoothThermal.disconnect;
      return result;
    } catch (e) {
      return false;
    }
  }

  /// Check connection status
  static Future<bool> isConnected() async {
    try {
      return await PrintBluetoothThermal.connectionStatus;
    } catch (e) {
      return false;
    }
  }

  /// Build ESC/POS byte sequence for a Sale Invoice (58mm or 80mm)
  static Future<List<int>> generateSaleReceiptBytes({
    required Map<String, dynamic> store,
    required Map<String, dynamic> invoice,
    PaperSize paperSize = PaperSize.mm58,
  }) async {
    final profile = await CapabilityProfile.load();
    final generator = Generator(paperSize, profile);
    List<int> bytes = [];

    bytes += generator.reset();

    // 1. Store Header
    bytes += generator.text(
      store['name'] ?? 'POS STORE',
      styles: const PosStyles(
        align: PosAlign.center,
        height: PosTextSize.size2,
        width: PosTextSize.size2,
        bold: true,
      ),
    );

    if (store['address'] != null && store['address'].toString().isNotEmpty) {
      bytes += generator.text(
        store['address'],
        styles: const PosStyles(align: PosAlign.center),
      );
    }

    if (store['phone'] != null && store['phone'].toString().isNotEmpty) {
      bytes += generator.text(
        'Tel: ${store['phone']}',
        styles: const PosStyles(align: PosAlign.center),
      );
    }

    if (store['tax_number'] != null && store['tax_number'].toString().isNotEmpty) {
      bytes += generator.text(
        'VAT/GST Reg: ${store['tax_number']}',
        styles: const PosStyles(align: PosAlign.center),
      );
    }

    bytes += generator.hr();

    // 2. Invoice Details
    bytes += generator.text('Invoice: ${invoice['invoice_number']}');
    bytes += generator.text('Date: ${invoice['date']}');
    bytes += generator.text('Cashier: ${invoice['cashier']}');
    bytes += generator.text('Customer: ${invoice['customer']}');
    bytes += generator.hr();

    // 3. Item List Table
    if (paperSize == PaperSize.mm58) {
      // 58mm: 32 columns grid (6 title, 2 qty, 2 price, 2 total)
      bytes += generator.row([
        PosColumn(text: 'Item', width: 6, styles: const PosStyles(bold: true)),
        PosColumn(text: 'Qty', width: 2, styles: const PosStyles(bold: true, align: PosAlign.center)),
        PosColumn(text: 'Price', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
        PosColumn(text: 'Total', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
      ]);

      final List items = invoice['items'] ?? [];
      for (final item in items) {
        bytes += generator.row([
          PosColumn(text: item['name'] ?? '', width: 6),
          PosColumn(text: '${item['quantity']}', width: 2, styles: const PosStyles(align: PosAlign.center)),
          PosColumn(text: '${item['unit_price']}', width: 2, styles: const PosStyles(align: PosAlign.right)),
          PosColumn(text: '${item['subtotal']}', width: 2, styles: const PosStyles(align: PosAlign.right)),
        ]);
      }
    } else {
      // 80mm: 48 columns grid
      bytes += generator.row([
        PosColumn(text: 'Item Description', width: 6, styles: const PosStyles(bold: true)),
        PosColumn(text: 'Qty', width: 2, styles: const PosStyles(bold: true, align: PosAlign.center)),
        PosColumn(text: 'Unit Price', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
        PosColumn(text: 'Subtotal', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
      ]);

      final List items = invoice['items'] ?? [];
      for (final item in items) {
        bytes += generator.row([
          PosColumn(text: item['name'] ?? '', width: 6),
          PosColumn(text: '${item['quantity']}', width: 2, styles: const PosStyles(align: PosAlign.center)),
          PosColumn(text: '${item['unit_price']}', width: 2, styles: const PosStyles(align: PosAlign.right)),
          PosColumn(text: '${item['subtotal']}', width: 2, styles: const PosStyles(align: PosAlign.right)),
        ]);
      }
    }

    bytes += generator.hr();

    // 4. Financial Calculations
    final sym = store['currency_symbol'] ?? '₹';

    bytes += generator.row([
      PosColumn(text: 'Subtotal:', width: 6),
      PosColumn(text: '$sym${invoice['subtotal']}', width: 6, styles: const PosStyles(align: PosAlign.right)),
    ]);

    if ((invoice['discount'] ?? 0) > 0) {
      bytes += generator.row([
        PosColumn(text: 'Discount:', width: 6),
        PosColumn(text: '-$sym${invoice['discount']}', width: 6, styles: const PosStyles(align: PosAlign.right)),
      ]);
    }

    if ((invoice['tax'] ?? 0) > 0) {
      bytes += generator.row([
        PosColumn(text: 'Tax/VAT:', width: 6),
        PosColumn(text: '+$sym${invoice['tax']}', width: 6, styles: const PosStyles(align: PosAlign.right)),
      ]);
    }

    bytes += generator.row([
      PosColumn(text: 'Grand Total:', width: 6, styles: const PosStyles(bold: true)),
      PosColumn(text: '$sym${invoice['grand_total']}', width: 6, styles: const PosStyles(align: PosAlign.right, bold: true)),
    ]);

    bytes += generator.row([
      PosColumn(text: 'Paid Amount (${invoice['payment_method']}):', width: 6),
      PosColumn(text: '$sym${invoice['paid_amount']}', width: 6, styles: const PosStyles(align: PosAlign.right)),
    ]);

    if ((invoice['due_amount'] ?? 0) > 0) {
      bytes += generator.row([
        PosColumn(text: 'Due Amount:', width: 6, styles: const PosStyles(bold: true)),
        PosColumn(text: '$sym${invoice['due_amount']}', width: 6, styles: const PosStyles(align: PosAlign.right, bold: true)),
      ]);
    }

    bytes += generator.hr();

    // 5. Store Footer Note
    if (store['footer'] != null && store['footer'].toString().isNotEmpty) {
      bytes += generator.text(
        store['footer'],
        styles: const PosStyles(align: PosAlign.center),
      );
    } else {
      bytes += generator.text(
        'Thank you for your business!',
        styles: const PosStyles(align: PosAlign.center, bold: true),
      );
    }

    bytes += generator.feed(2);
    bytes += generator.cut();

    return bytes;
  }

  /// Print test voucher to verify printer width and alignment
  static Future<List<int>> generateTestReceiptBytes({PaperSize paperSize = PaperSize.mm58}) async {
    final profile = await CapabilityProfile.load();
    final generator = Generator(paperSize, profile);
    List<int> bytes = [];

    bytes += generator.reset();
    bytes += generator.text(
      'THERMAL PRINTER TEST',
      styles: const PosStyles(
        align: PosAlign.center,
        bold: true,
        height: PosTextSize.size2,
        width: PosTextSize.size2,
      ),
    );
    bytes += generator.text(
      'Paper Mode: ${paperSize == PaperSize.mm58 ? "58mm (2-inch)" : "80mm (3-inch)"}',
      styles: const PosStyles(align: PosAlign.center),
    );
    bytes += generator.hr();
    bytes += generator.text('Bluetooth Thermal Connection OK!', styles: const PosStyles(align: PosAlign.center));
    bytes += generator.feed(2);
    bytes += generator.cut();

    return bytes;
  }

  /// Send raw byte buffer to printer
  static Future<bool> sendBytesToPrinter(List<int> bytes) async {
    try {
      final isConnected = await PrintBluetoothThermal.connectionStatus;
      if (!isConnected) return false;
      return await PrintBluetoothThermal.writeBytes(bytes);
    } catch (e) {
      return false;
    }
  }
}
