<?php

require_once 'AppController.php';
require_once __DIR__ . '/../services/PaymentService.php';

class PaymentController extends AppController
{
    private $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    public function addPayment()
    {
        if (!$this->isPost()) {
            http_response_code(405);
            exit();
        }

        $result = $this->paymentService->addPayment($_POST);
        $this->sendJsonResponse($result);
    }

    private function sendJsonResponse(array $result)
    {
        header('Content-type: application/json');
        if (isset($result['code'])) {
            http_response_code($result['code']);
        }
        echo json_encode($result);
        exit();
    }
}
