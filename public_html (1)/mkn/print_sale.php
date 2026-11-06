<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if a sale ID is provided
if (!isset($_GET['id'])) {
    header("Location: sales_list.php?message=❌ No sale ID provided.");
    exit();
}

$sale_id = $_GET['id'];

// Fetch sale details
$sql = "SELECT s.*, c.customer_name, c.shop_name, c.city, c.state, c.phone, c.email
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale_result = $stmt->get_result();

if ($sale_result->num_rows === 0) {
    header("Location: sales_list.php?message=❌ Sale not found.");
    exit();
}

$sale = $sale_result->fetch_assoc();

// Fetch sale items
$items_sql = "SELECT si.quantity, si.rate, si.mrp, si.discount, p.product_name, p.piece_weight, si.taxable_value
              FROM sale_items si
              LEFT JOIN products p ON si.product_id = p.id
              WHERE si.sale_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$sale_items = [];
while ($row = $items_result->fetch_assoc()) {
    $sale_items[] = $row;
}

// Function to convert numbers to words
function convert_number_to_words($number) {
    $hyphen = '-';
    $conjunction = ' and ';
    $separator = ', ';
    $negative = 'negative ';
    $dictionary = array(
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
    );
    $hundred = 'hundred';
    $thousand = 'thousand';
    $lakh = 'lakh';
    $crore = 'crore';

    if (!is_numeric($number)) return false;
    if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
        return 'too large';
    }
    $number = (string) (int)$number;
    $number = str_replace(',', '', $number);
    if ($number[0] == '-') {
        $negative = 'negative ';
        $number = substr($number, 1);
    } else {
        $negative = '';
    }

    $amount_words = [];
    $amount_number = explode('.', $number);
    $integer_part = (int)$amount_number[0];
    
    if ($integer_part == 0) {
        $amount_words[] = $dictionary[0];
    } else {
        $crore_part = floor($integer_part / 10000000);
        $lakh_part = floor(($integer_part % 10000000) / 100000);
        $thousand_part = floor((($integer_part % 100000) / 1000));
        $remainder = $integer_part % 1000;

        if ($crore_part > 0) {
            $amount_words[] = convert_number_to_words($crore_part) . ' ' . $crore;
        }
        if ($lakh_part > 0) {
            $amount_words[] = convert_number_to_words($lakh_part) . ' ' . $lakh;
        }
        if ($thousand_part > 0) {
            $amount_words[] = convert_number_to_words($thousand_part) . ' ' . $thousand;
        }

        if ($remainder > 0) {
            $hundreds = floor($remainder / 100);
            $tens = $remainder % 100;
            if ($hundreds > 0) {
                $amount_words[] = $dictionary[$hundreds] . ' ' . $hundred;
            }
            if ($tens > 0) {
                if ($tens < 20) {
                    $amount_words[] = $dictionary[$tens];
                } else {
                    $amount_words[] = $dictionary[floor($tens / 10) * 10] . ($tens % 10 > 0 ? $hyphen . $dictionary[$tens % 10] : '');
                }
            }
        }
    }

    return $negative . implode($separator, $amount_words);
}

// Convert grand total to words
$total_in_words = ucwords(convert_number_to_words((int)$sale['grand_total'])) . ' Only';

// ---------------------------
// New bank details: replace with actual values from your passbook image
// If you want them hard-coded, replace the placeholders below.
// ---------------------------
$bank_name = "Indian Overseas Bank";     // <-- replace as necessary
$account_holder = "Nagaraj.G";          // <-- replace as necessary
$branch = "Kovilpatti";                  // <-- replace as necessary
$account_no = "077801000046666";        // <-- replace as necessary
$ifsc = "IOBA0000778";                   // <-- replace as necessary
// ---------------------------

$digital_payment_line = "GPay / Paytm / PhonePe - +91 78269 56973 | Name: Nagaraj G";

// Path to the stamp image uploaded in the conversation (used as signature)
// Make sure web server can access files in /mnt/data or move the image into your project public folder and update this path accordingly.
$stamp_image_path = '/kadalai/stamp.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($sale['invoice_no']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            color: #212121;
            margin: 0;
            padding: 20px;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .header-table td {
            padding: 5px;
            vertical-align: top;
            text-align: center;
        }
        .header-table .company-info h1 {
            font-size: 24px;
            margin: 0;
            color: #0F1440;
        }
        .header-table .company-info p {
            margin: 2px 0;
            font-size: 12px;
            line-height: 1.2;
        }
        .header-info-table, .customer-info-table {
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .header-info-table td, .customer-info-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .product-table th, .product-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .product-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .product-table .product-col { text-align: left; }
        .product-table .qty-col { width: 50px; text-align: center; }
        .product-table .numeric-col { text-align: right; }
        .product-table .sno-col { width: 30px; text-align: center; }
        .footer-table td {
            padding: 8px;
            vertical-align: top;
            border: 1px solid #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .invoice-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .signature-img {
            max-width: 220px;
            height: auto;
            display: block;
            margin: 0 auto;
            opacity: 0.98;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                border: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <table class="header-table">
            <tr>
                <td style="width: 100%;" class="company-info">
                    <h1 style="color: #000; font-family: sans-serif; font-size: 24px;">MKN KADALAIMITTAI & SNACKS</h1>
                    <p>175/4, Kilaku Pandavarmangalam, Kovilpatti, Tamil Nadu, 628502</p>
                </td>
            </tr>
        </table>

        <table class="header-info-table">
            <tr>
                <td style="width: 50%;">
                    <p><strong>GST No:</strong> 33ANCPG8479J1ZP</p>
                    <p><strong>Email:</strong> mknkadalaimittai@gmail.com</p>
                    <p><strong>Phone:</strong> +91 88706 00855, +91 78269 56973</p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($sale['invoice_no']); ?></p>
                    <p><strong>Invoice Date:</strong> <?php echo date('d.m.Y', strtotime($sale['billing_date'])); ?></p>
                </td>
            </tr>
        </table>

        <table class="customer-info-table">
            <tr>
                <td style="width: 50%;">
                    <p class="font-bold">M/s <?php echo htmlspecialchars($sale['shop_name']); ?></p>
                    <p>To: <?php echo htmlspecialchars($sale['customer_name']); ?></p>
                    <p>City: <?php echo htmlspecialchars($sale['city']); ?></p>
                    <p>State: <?php echo htmlspecialchars($sale['state']); ?></p>
                </td>
                <td style="width: 50%;">
                    <p><strong>TRANSPORT:</strong> <?php echo htmlspecialchars($sale['transport_mode']); ?> <?php echo !empty($sale['custom_transport']) ? '(' . htmlspecialchars($sale['custom_transport']) . ')' : ''; ?></p>
                    <p><strong>State Code:</strong> <?php echo htmlspecialchars($sale['state'] == 'Tamil Nadu' ? '33' : 'N/A'); ?></p>
                </td>
            </tr>
        </table>

        <table class="product-table">
            <thead>
                <tr>
                    <th class="sno-col">S.No</th>
                    <th>Product Description</th>
                    <th>HSN</th>
                    <th>UOB</th>
                    <th class="numeric-col">M.R.P.</th>
                    <th class="qty-col">Qty</th>
                    <th class="numeric-col">Rate</th>
                    <th class="numeric-col">Amount</th>
                    <th class="numeric-col">Discount</th>
                    <th class="numeric-col">Taxable Value</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; ?>
                <?php foreach ($sale_items as $item): ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td>1704</td>
                        <td><?php echo htmlspecialchars($item['piece_weight'] ?: 'N/A'); ?></td>
                        <td class="text-right"><?php echo number_format($item['mrp'], 2); ?></td>
                        <td class="text-center"><?php echo intval($item['quantity']); ?></td>
                        <td class="text-right"><?php echo number_format($item['rate'], 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['rate'] * $item['quantity'], 2); ?></td>
                        <td class="text-right"><?php echo number_format($item['discount'], 2); ?>%</td>
                        <td class="text-right"><?php echo number_format($item['taxable_value'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    <strong>Total Invoice Amount in Words</strong>
                    <br><br>
                    Rupees: <?php echo htmlspecialchars($total_in_words); ?>
                </td>
                <td style="width: 50%;">
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td class="text-right font-bold">Total Amount before Tax</td>
                            <td class="text-right font-bold">₹<?php echo number_format($sale['total_taxable_value'], 2); ?></td>
                        </tr>
                        <?php if ($sale['apply_gst']): ?>
                        <tr>
                            <td class="text-right">Add CGST (2.5%)</td>
                            <td class="text-right">₹<?php echo number_format($sale['cgst'], 2); ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">Add SGST (2.5%)</td>
                            <td class="text-right">₹<?php echo number_format($sale['sgst'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-right">Add Transport Cost</td>
                            <td class="text-right">₹<?php echo number_format($sale['transport_cost'], 2); ?></td>
                        </tr>
                        <tr style="font-size: 16px;">
                            <td class="text-right font-bold">Grand Total</td>
                            <td class="text-right font-bold">₹<?php echo number_format($sale['grand_total'], 2); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="font-bold">Bank Details</p>
                    <p><strong>BANK NAME:</strong> <?php echo htmlspecialchars($bank_name); ?></p>
                    <p><strong>ACCOUNT HOLDER NAME:</strong> <?php echo htmlspecialchars($account_holder); ?></p>
                    <p><strong>BRANCH:</strong> <?php echo htmlspecialchars($branch); ?></p>
                    <p><strong>ACCOUNT NO:</strong> <?php echo htmlspecialchars($account_no); ?></p>
                    <p><strong>IFSC Code:</strong> <?php echo htmlspecialchars($ifsc); ?></p>
                    <p style="margin-top:6px;"><strong>Digital Payments:</strong> <?php echo htmlspecialchars($digital_payment_line); ?></p>
                </td>
               
                <td class="text-center" style="vertical-align: middle;">
                    <p>MKN KADALAIMITTAI & SNACKS</p>
                    
                    <p class="font-bold">Authorised Signature</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="font-size: 10px;">
                    This is an auto generated invoice.
                </td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
