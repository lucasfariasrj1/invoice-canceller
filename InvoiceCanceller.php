<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function InvoiceCanceller_config() {
    return [
        'name' => 'Invoice Canceller',
        'description' => 'Módulo para cancelar faturas não pagas e pedidos relacionados em um período selecionado.',
        'version' => '1.0',
        'author' => 'Seu Nome',
        'fields' => []
    ];
}

function InvoiceCanceller_output($vars) {
    echo '<div class="container">';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['confirm'])) {

            $period = $_POST['period'];
            cancelInvoicesAndOrders($period);
        } else {
            $period = $_POST['period'];
            showInvoicesAndOrdersToCancel($period);
        }
    } else {
        echo '
        <div class="form-container">
            <h2>Cancelar Faturas e Pedidos Não Pagos</h2>
            <form method="post">
                <label for="period">Selecione o período (dias desde o vencimento):</label>
                <input type="number" name="period" id="period" class="input-field" required>
                <button type="submit" class="btn-primary">Ver faturas e pedidos</button>
            </form>
        </div>';
    }

    echo '</div>';

    echo '<style>
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            font-size: 24px;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #555;
        }
        .input-field {
            width: 50%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn-primary {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        td {
            background-color: #fff;
        }
        .btn-confirm {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-confirm:hover {
            background-color: #e53935;
        }
        p {
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }
    </style>';
}

function showInvoicesAndOrdersToCancel($period) {
    $query = "SELECT id, total, duedate FROM tblinvoices WHERE status = 'Unpaid' AND DATEDIFF(NOW(), duedate) >= ?";
    $results = Capsule::select($query, [$period]);

    if (count($results) > 0) {
        echo "<h3>Faturas e pedidos que serão cancelados:</h3>";
        echo '<form method="post">';
        echo '<input type="hidden" name="period" value="' . $period . '">';
        echo '<table>
                <tr>
                    <th>Invoice ID</th>
                    <th>Total</th>
                    <th>Data de Vencimento</th>
                    <th>Pedidos Relacionados</th>
                </tr>';
        
        foreach ($results as $invoice) {
            echo '<tr>';
            echo '<td>' . $invoice->id . '</td>';
            echo '<td>' . $invoice->total . '</td>';
            echo '<td>' . $invoice->duedate . '</td>';

            $orderQuery = "SELECT id FROM tblorders WHERE invoiceid = ?";
            $orders = Capsule::select($orderQuery, [$invoice->id]);

            echo '<td>';
            if (count($orders) > 0) {
                foreach ($orders as $order) {
                    echo 'Pedido ID: ' . $order->id . '<br>';
                }
            } else {
                echo 'Nenhum pedido relacionado';
            }
            echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<button type="submit" name="confirm" class="btn-confirm">Confirmar Cancelamento</button>';
                    echo '</form>';
                } else {
                    echo "<p>Nenhuma fatura não paga foi encontrada no período selecionado.</p>";
                }
}

function cancelInvoicesAndOrders($period) {

    $query = "SELECT id FROM tblinvoices WHERE status = 'Unpaid' AND DATEDIFF(NOW(), duedate) >= ?";
    $results = Capsule::select($query, [$period]);

    foreach ($results as $invoice) {

        $command = 'UpdateInvoice';
        $postData = [
            'invoiceid' => $invoice->id,
            'status' => 'Cancelled',
        ];
        $results = localAPI($command, $postData);


        $orderQuery = "SELECT orderid FROM tblorders WHERE invoiceid = ?";
        $orders = Capsule::select($orderQuery, [$invoice->id]);

        foreach ($orders as $order) {
            $cancelOrderCommand = 'CancelOrder';
            $orderData = [
                'orderid' => $order->orderid,
            ];
            $cancelOrderResult = localAPI($cancelOrderCommand, $orderData);
        }
    }

    echo "<p>Faturas e pedidos foram cancelados com sucesso!</p>";
}
?>
