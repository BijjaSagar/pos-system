import 'package:flutter/foundation.dart';
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

  /// Clean text so ESC/POS thermal printers (CP437) never crash on emojis or non-ASCII currency signs
  static String sanitizeForThermal(dynamic input) {
    if (input == null) return '';
    String s = input.toString();
    s = s.replaceAll('₹', 'Rs.');
    s = s.replaceAll('☕', '');
    s = s.replaceAll('•', '-');
    s = s.replaceAll('—', '-');
    s = s.replaceAll('–', '-');
    s = s.replaceAll('“', '"').replaceAll('”', '"');
    s = s.replaceAll('‘', "'").replaceAll('’', "'");
    // Strip non-printable / non-ascii characters to guarantee 100% printer compatibility
    return s.replaceAll(RegExp(r'[^\x20-\x7E\r\n\t]'), '').trim();
  }

  /// Clean numeric amount string cleanly formatted to 2 decimal places
  static String sanitizeAmount(dynamic input) {
    if (input == null) return '0.00';
    String s = input.toString().replaceAll('₹', '').replaceAll('Rs.', '').replaceAll('Rs', '').trim();
    final d = double.tryParse(s);
    if (d != null) {
      return d.toStringAsFixed(2);
    }
    return s.replaceAll(RegExp(r'[^\d.]'), '');
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
    final storeName = sanitizeForThermal(store['name'] ?? 'POS STORE');
    bytes += generator.text(
      storeName,
      styles: const PosStyles(
        align: PosAlign.center,
        height: PosTextSize.size2,
        width: PosTextSize.size2,
        bold: true,
      ),
    );

    if (store['header'] != null && store['header'].toString().isNotEmpty) {
      final headerLines = store['header'].toString().split('\n');
      for (final line in headerLines) {
        final cleanLine = sanitizeForThermal(line);
        if (cleanLine.isNotEmpty) {
          bytes += generator.text(cleanLine, styles: const PosStyles(align: PosAlign.center));
        }
      }
    }

    final storeAddress = sanitizeForThermal(store['address']);
    if (storeAddress.isNotEmpty) {
      bytes += generator.text(
        storeAddress,
        styles: const PosStyles(align: PosAlign.center),
      );
    }

    final storePhone = sanitizeForThermal(store['phone']);
    if (storePhone.isNotEmpty) {
      bytes += generator.text(
        'Tel: $storePhone',
        styles: const PosStyles(align: PosAlign.center),
      );
    }

    final taxNumber = sanitizeForThermal(store['tax_number']);
    if (taxNumber.isNotEmpty) {
      bytes += generator.text(
        'GSTIN: $taxNumber',
        styles: const PosStyles(align: PosAlign.center),
      );
    }

    bytes += generator.hr();

    // 2. Invoice Details
    bytes += generator.text('Invoice: ${sanitizeForThermal(invoice['invoice_number'])}');
    bytes += generator.text('Date: ${sanitizeForThermal(invoice['date'])}');
    bytes += generator.text('Cashier: ${sanitizeForThermal(invoice['cashier'])}');
    bytes += generator.text('Customer: ${sanitizeForThermal(invoice['customer'])}');
    bytes += generator.hr();

    // 3. Item List Table
    if (paperSize == PaperSize.mm58) {
      // 58mm: 12 columns total (6 title, 2 qty, 2 price, 2 total)
      bytes += generator.row([
        PosColumn(text: 'Item', width: 6, styles: const PosStyles(bold: true)),
        PosColumn(text: 'Qty', width: 2, styles: const PosStyles(bold: true, align: PosAlign.center)),
        PosColumn(text: 'Rate', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
        PosColumn(text: 'Amt', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
      ]);

      final List items = invoice['items'] ?? [];
      for (final item in items) {
        final itemName = sanitizeForThermal(item['name'] ?? 'Item');
        final itemQty = sanitizeForThermal(item['quantity'] ?? '1');
        final itemPrice = sanitizeAmount(item['unit_price']);
        final itemTotal = sanitizeAmount(item['subtotal']);

        bytes += generator.row([
          PosColumn(text: itemName, width: 6),
          PosColumn(text: itemQty, width: 2, styles: const PosStyles(align: PosAlign.center)),
          PosColumn(text: itemPrice, width: 2, styles: const PosStyles(align: PosAlign.right)),
          PosColumn(text: itemTotal, width: 2, styles: const PosStyles(align: PosAlign.right)),
        ]);
      }
    } else {
      // 80mm: 12 columns grid (6 title, 2 qty, 2 price, 2 total)
      bytes += generator.row([
        PosColumn(text: 'Item Description', width: 6, styles: const PosStyles(bold: true)),
        PosColumn(text: 'Qty', width: 2, styles: const PosStyles(bold: true, align: PosAlign.center)),
        PosColumn(text: 'Rate', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
        PosColumn(text: 'Total', width: 2, styles: const PosStyles(bold: true, align: PosAlign.right)),
      ]);

      final List items = invoice['items'] ?? [];
      for (final item in items) {
        final itemName = sanitizeForThermal(item['name'] ?? 'Item');
        final itemQty = sanitizeForThermal(item['quantity'] ?? '1');
        final itemPrice = sanitizeAmount(item['unit_price']);
        final itemTotal = sanitizeAmount(item['subtotal']);

        bytes += generator.row([
          PosColumn(text: itemName, width: 6),
          PosColumn(text: itemQty, width: 2, styles: const PosStyles(align: PosAlign.center)),
          PosColumn(text: itemPrice, width: 2, styles: const PosStyles(align: PosAlign.right)),
          PosColumn(text: itemTotal, width: 2, styles: const PosStyles(align: PosAlign.right)),
        ]);
      }
    }

    bytes += generator.hr();

    // 4. Financial Calculations (Clean ASCII Rs.)
    const String cur = 'Rs. ';

    bytes += generator.row([
      PosColumn(text: 'Subtotal:', width: 6),
      PosColumn(text: '$cur${sanitizeAmount(invoice['subtotal'])}', width: 6, styles: const PosStyles(align: PosAlign.right)),
    ]);

    final discountVal = double.tryParse('${invoice['discount']}') ?? 0;
    if (discountVal > 0) {
      bytes += generator.row([
        PosColumn(text: 'Discount:', width: 6),
        PosColumn(text: '-$cur${discountVal.toStringAsFixed(2)}', width: 6, styles: const PosStyles(align: PosAlign.right)),
      ]);
    }

    final taxVal = double.tryParse('${invoice['tax']}') ?? 0;
    if (taxVal > 0) {
      bytes += generator.row([
        PosColumn(text: 'GST / Tax:', width: 6),
        PosColumn(text: '+$cur${taxVal.toStringAsFixed(2)}', width: 6, styles: const PosStyles(align: PosAlign.right)),
      ]);
    }

    bytes += generator.row([
      PosColumn(text: 'Grand Total:', width: 6, styles: const PosStyles(bold: true)),
      PosColumn(text: '$cur${sanitizeAmount(invoice['grand_total'])}', width: 6, styles: const PosStyles(align: PosAlign.right, bold: true)),
    ]);

    final payMethod = sanitizeForThermal(invoice['payment_method'] ?? 'CASH').toUpperCase();
    bytes += generator.row([
      PosColumn(text: 'Paid ($payMethod):', width: 6),
      PosColumn(text: '$cur${sanitizeAmount(invoice['paid_amount'])}', width: 6, styles: const PosStyles(align: PosAlign.right)),
    ]);

    final dueVal = double.tryParse('${invoice['due_amount']}') ?? 0;
    if (dueVal > 0) {
      bytes += generator.row([
        PosColumn(text: 'Due Balance:', width: 6, styles: const PosStyles(bold: true)),
        PosColumn(text: '$cur${dueVal.toStringAsFixed(2)}', width: 6, styles: const PosStyles(align: PosAlign.right, bold: true)),
      ]);
    }

    bytes += generator.hr();

    // 5. Store Footer Note
    final footer = sanitizeForThermal(store['footer']);
    if (footer.isNotEmpty) {
      final footerLines = footer.split('\n');
      for (final fLine in footerLines) {
        final cleanFLine = sanitizeForThermal(fLine);
        if (cleanFLine.isNotEmpty) {
          bytes += generator.text(cleanFLine, styles: const PosStyles(align: PosAlign.center));
        }
      }
    } else {
      bytes += generator.text(
        'Thank you for your visit!',
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
      if (!isConnected) {
        debugPrint('ThermalPrinterService: Bluetooth printer not connected.');
        return false;
      }
      final res = await PrintBluetoothThermal.writeBytes(bytes);
      debugPrint('ThermalPrinterService: writeBytes output: $res (${bytes.length} bytes)');
      return res;
    } catch (e, stack) {
      debugPrint('ThermalPrinterService: sendBytesToPrinter error: $e\n$stack');
      return false;
    }
  }
}
