<?php
use WHMCS\Database\Capsule;

require_once __DIR__ . '/init.php';

function cancelUnpaidInvoicesAndOrders($period = 15) {
    $query = "SELECT id, total, duedate FROM tblinvoices WHERE status = 'Unpaid' AND DATEDIFF(NOW(), duedate) >= ?";
    $results = Capsule::select($query, [$period]);

    if (count($results) > 0) {
        foreach ($results as $invoice) {
            $command = 'UpdateInvoice';
            $postData = [
                'invoiceid' => $invoice->id,
                'status' => 'Cancelled',
            ];
            $result = localAPI($command, $postData);

            if ($result['result'] == 'success') {
                echo "Fatura ID: {$invoice->id} cancelada com sucesso.\n";

                $orderQuery = "SELECT id FROM tblorders WHERE invoiceid = ?";
                $orders = Capsule::select($orderQuery, [$invoice->id]);

                foreach ($orders as $order) {
                    $cancelOrderCommand = 'CancelOrder';
                    $orderData = [
                        'orderid' => $order->id,
                    ];
                    $cancelOrderResult = localAPI($cancelOrderCommand, $orderData);

                    if ($cancelOrderResult['result'] == 'success') {
                        echo "Pedido ID: {$order->id} cancelado com sucesso.\n";
                    } else {
                        echo "Falha ao cancelar Pedido ID: {$order->id}.\n";
                    }
                }
            } else {
                echo "Falha ao cancelar Fatura ID: {$invoice->id}.\n";
            }
        }
    } else {
        echo "Nenhuma fatura não paga encontrada no período de $period dias.\n";
    }
}

cancelUnpaidInvoicesAndOrders(15);
