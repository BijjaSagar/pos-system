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

  /// Format date string cleanly (e.g. 18/10/2026 05:43) avoiding ISO overflow
  static String formatDate(dynamic input) {
    if (input == null) return '';
    String s = input.toString().trim();
    try {
      final dt = DateTime.parse(s);
      final y = dt.year.toString().padLeft(4, '0');
      final m = dt.month.toString().padLeft(2, '0');
      final d = dt.day.toString().padLeft(2, '0');
      final h = dt.hour.toString().padLeft(2, '0');
      final min = dt.minute.toString().padLeft(2, '0');
      return '$d/$m/$y $h:$min';
    } catch (_) {
      if (s.contains('T')) {
        s = s.replaceAll('T', ' ');
      }
      if (s.contains('.')) {
        s = s.split('.').first;
      }
      if (s.endsWith('Z')) {
        s = s.substring(0, s.length - 1);
      }
      return s.trim();
    }
  }

  /// Wrap text strictly on word boundaries so words are NEVER split across lines
  static List<String> wrapWords(String text, int maxLineLength) {
    if (text.isEmpty) return [];
    final List<String> result = [];
    final words = text.split(RegExp(r'\s+'));
    String currentLine = '';

    for (final word in words) {
      if (word.isEmpty) continue;
      if (currentLine.isEmpty) {
        if (word.length <= maxLineLength) {
          currentLine = word;
        } else {
          result.add(word.substring(0, maxLineLength));
          currentLine = word.substring(maxLineLength);
        }
      } else if (currentLine.length + 1 + word.length <= maxLineLength) {
        currentLine += ' $word';
      } else {
        result.add(currentLine);
        currentLine = word;
      }
    }
    if (currentLine.isNotEmpty) {
      result.add(currentLine);
    }
    return result;
  }

  /// Align a key-value label pair to fit precisely within a given monospace line length
  static String formatKeyValueLine(String label, String value, int maxLineLength) {
    final cleanLabel = label.trim();
    final cleanValue = value.trim();
    final totalLen = cleanLabel.length + cleanValue.length;
    if (totalLen >= maxLineLength) {
      final availableLabel = maxLineLength - cleanValue.length - 1;
      final truncatedLabel = availableLabel > 0 ? cleanLabel.substring(0, availableLabel) : cleanLabel;
      final spaces = ' ' * (maxLineLength - truncatedLabel.length - cleanValue.length);
      return '$truncatedLabel$spaces$cleanValue';
    }
    final spaces = ' ' * (maxLineLength - totalLen);
    return '$cleanLabel$spaces$cleanValue';
  }

  /// Format an item entry so it strictly occupies a SINGLE ROW on thermal printouts
  static String formatSingleRowItem({
    required String name,
    required String qty,
    required String rate,
    required String amount,
    required PaperSize paperSize,
  }) {
    if (paperSize == PaperSize.mm58) {
      // 58mm: Exactly 32 characters
      // Format: Name(14) + ' ' + Qty(3) + ' ' + Rate(6) + ' ' + Amt(6) = 32 chars
      String cleanName = name.trim();
      if (cleanName.length > 14) {
        cleanName = '${cleanName.substring(0, 13)}.';
      }
      final colName = cleanName.padRight(14);
      final colQty = qty.padLeft(3);
      final colRate = rate.padLeft(6);
      final colAmt = amount.padLeft(6);
      return '$colName $colQty $colRate $colAmt';
    } else {
      // 80mm: Exactly 48 characters
      // Format: Name(22) + ' ' + Qty(4) + ' ' + Rate(9) + ' ' + Amt(10) = 48 chars
      String cleanName = name.trim();
      if (cleanName.length > 22) {
        cleanName = '${cleanName.substring(0, 21)}.';
      }
      final colName = cleanName.padRight(22);
      final colQty = qty.padLeft(4);
      final colRate = rate.padLeft(9);
      final colAmt = amount.padLeft(10);
      return '$colName $colQty $colRate $colAmt';
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

    final maxNormalChars = paperSize == PaperSize.mm58 ? 32 : 48;
    final maxHeaderChars = paperSize == PaperSize.mm58 ? 16 : 24;

    // 1. Store Header
    final storeName = sanitizeForThermal(store['name'] ?? 'NesCafe Solapur');
    for (final nameLine in wrapWords(storeName, maxHeaderChars)) {
      bytes += generator.text(
        nameLine,
        styles: const PosStyles(
          align: PosAlign.center,
          height: PosTextSize.size2,
          width: PosTextSize.size2,
          bold: true,
        ),
      );
    }

    final headerRaw = store['header']?.toString() ?? '';
    if (headerRaw.isNotEmpty) {
      for (final rawLine in headerRaw.split('\n')) {
        final cleanLine = sanitizeForThermal(rawLine);
        for (final wLine in wrapWords(cleanLine, maxNormalChars)) {
          bytes += generator.text(wLine, styles: const PosStyles(align: PosAlign.center));
        }
      }
    }

    final storeAddress = sanitizeForThermal(store['address']);
    if (storeAddress.isNotEmpty) {
      for (final wLine in wrapWords(storeAddress, maxNormalChars)) {
        bytes += generator.text(wLine, styles: const PosStyles(align: PosAlign.center));
      }
    }

    final storePhone = sanitizeForThermal(store['phone']);
    if (storePhone.isNotEmpty) {
      for (final wLine in wrapWords('Tel: $storePhone', maxNormalChars)) {
        bytes += generator.text(wLine, styles: const PosStyles(align: PosAlign.center));
      }
    }

    // De-duplicate GSTIN if already included in header
    final taxNumber = sanitizeForThermal(store['tax_number']);
    if (taxNumber.isNotEmpty && !headerRaw.contains(taxNumber)) {
      for (final wLine in wrapWords('GSTIN: $taxNumber', maxNormalChars)) {
        bytes += generator.text(wLine, styles: const PosStyles(align: PosAlign.center));
      }
    }

    bytes += generator.hr();

    // 2. Invoice Details (Cleanly aligned key-value pairs)
    bytes += generator.text(formatKeyValueLine('Invoice:', sanitizeForThermal(invoice['invoice_number']), maxNormalChars));
    bytes += generator.text(formatKeyValueLine('Date:', formatDate(invoice['date']), maxNormalChars));
    bytes += generator.text(formatKeyValueLine('Cashier:', sanitizeForThermal(invoice['cashier']), maxNormalChars));
    bytes += generator.text(formatKeyValueLine('Customer:', sanitizeForThermal(invoice['customer']), maxNormalChars));
    bytes += generator.hr();

    // 3. Item List Table - STRICTLY SINGLE ROW FOR EVERY ITEM
    if (paperSize == PaperSize.mm58) {
      // 32 chars header: Item(14) + ' ' + Qty(3) + ' ' + Rate(6) + ' ' + Amt(6) = 32
      bytes += generator.text(
        'Item           Qty   Rate    Amt',
        styles: const PosStyles(bold: true),
      );
    } else {
      // 48 chars header: Item(22) + ' ' + Qty(4) + ' ' + Rate(9) + ' ' + Amt(10) = 48
      bytes += generator.text(
        'Item Description       Qty     Rate     Amount',
        styles: const PosStyles(bold: true),
      );
    }

    final List items = invoice['items'] ?? [];
    for (final item in items) {
      final itemName = sanitizeForThermal(item['name'] ?? 'Item');
      final rawQty = double.tryParse('${item['quantity']}') ?? 1.0;
      final itemQty = rawQty == rawQty.toInt() ? '${rawQty.toInt()}' : rawQty.toStringAsFixed(1);
      final itemPrice = sanitizeAmount(item['unit_price']);
      final itemTotal = sanitizeAmount(item['subtotal']);

      final rowLine = formatSingleRowItem(
        name: itemName,
        qty: itemQty,
        rate: itemPrice,
        amount: itemTotal,
        paperSize: paperSize,
      );

      bytes += generator.text(rowLine);
    }

    bytes += generator.hr();

    // 4. Financial Calculations (Clean, aligned monospace lines)
    const String cur = 'Rs. ';

    bytes += generator.text(
      formatKeyValueLine('Subtotal:', '$cur${sanitizeAmount(invoice['subtotal'])}', maxNormalChars),
    );

    final discountVal = double.tryParse('${invoice['discount']}') ?? 0;
    if (discountVal > 0) {
      bytes += generator.text(
        formatKeyValueLine('Discount:', '-$cur${discountVal.toStringAsFixed(2)}', maxNormalChars),
      );
    }

    final taxVal = double.tryParse('${invoice['tax']}') ?? 0;
    if (taxVal > 0) {
      bytes += generator.text(
        formatKeyValueLine('GST / Tax:', '+$cur${taxVal.toStringAsFixed(2)}', maxNormalChars),
      );
    }

    bytes += generator.text(
      formatKeyValueLine('Grand Total:', '$cur${sanitizeAmount(invoice['grand_total'])}', maxNormalChars),
      styles: const PosStyles(bold: true),
    );

    final payMethod = sanitizeForThermal(invoice['payment_method'] ?? 'CASH').toUpperCase();
    bytes += generator.text(
      formatKeyValueLine('Paid ($payMethod):', '$cur${sanitizeAmount(invoice['paid_amount'])}', maxNormalChars),
    );

    final dueVal = double.tryParse('${invoice['due_amount']}') ?? 0;
    if (dueVal > 0) {
      bytes += generator.text(
        formatKeyValueLine('Due Balance:', '$cur${dueVal.toStringAsFixed(2)}', maxNormalChars),
        styles: const PosStyles(bold: true),
      );
    }

    bytes += generator.hr();

    // 5. Store Footer Note (Word-wrapped so words are NEVER broken)
    final footer = sanitizeForThermal(store['footer']);
    if (footer.isNotEmpty) {
      final footerLines = footer.split('\n');
      for (final fLine in footerLines) {
        final cleanFLine = sanitizeForThermal(fLine);
        for (final wLine in wrapWords(cleanFLine, maxNormalChars)) {
          bytes += generator.text(wLine, styles: const PosStyles(align: PosAlign.center));
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
