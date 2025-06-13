<?php

namespace TynaTech;

use Dotenv\Dotenv;

/**
 * InstinctClass - Simple PHP client for Instinct Veterinary API
 * 
 * A lightweight, easy-to-use PHP wrapper for the Instinct Partner API.
 * Handles authentication automatically and provides simple methods for common operations.
 * 
 * Available Methods (65+ total):
 * - Visits: getVisits($date, $params), createVisit($data), updateVisit($id, $data), checkOutVisit($id, $data)
 * - Accounts: getAccounts($params), getAccount($id), createAccount($data), updateAccount($id, $data)
 * - Appointment Types: getAppointmentTypes($params), getAppointmentType($id)
 * - Alerts: getAlerts($params), getAlert($id)
 * - Appointments: getAppointments($params), getAppointment($id), updateAppointment($id, $data), cancelAppointment($id, $data)
 * - Breeds: getBreeds($params)
 * - Dispensed Prescriptions: getDispensedPrescriptions($params), getDispensedPrescription($id)
 * - External Prescriptions: getExternalPrescriptions($params), getExternalPrescription($id)
 * - Invoices: getInvoices($params), getInvoice($id), createInvoice($data), createStandaloneInvoice($data)
 * - Invoice Ledger Entries: getInvoiceLedgerEntries($params)
 * - Invoice Line Items: getInvoiceLineItems($params), getInvoiceLineItem($id), addInvoiceLineItem($data)
 * - Locations: getLocations($params), getLocation($id)
 * - Orders: getOrder($id), getOrdersByVisit($visitId, $params)
 * - Treatments: getTreatmentsByOrder($orderId, $params), getTreatment($id)
 * - Patients: getPatients($params), getPatient($id), createPatient($data), updatePatient($id, $data), transferPatient($id, $data), uploadPatientFile($id, $fileData)
 * - Payment Transactions: getPaymentTransactions($params)
 * - PDFs: requestPdf($data), getPdfRequestStatus($id)
 * - Products: getProducts($params), getProduct($id)
 * - Reminder Labels: getReminderLabels($params), getReminderLabel($id)
 * - Reminders: getReminders($params), getReminder($id)
 * - Services: getServices($params), getService($id)
 * - Users & Titles: getTitles($params), getTitle($id), getUsers($params), getUser($id)
 * - HTTP Methods: get(), post(), put(), patch(), delete()
 * 
 * @author Rafael Kapela
 * @package TynaTech
 * @version 2.0.0
 * @license MIT
 * 
 * @example
 * // Initialize the client
 * $instinct = new InstinctClass();
 * 
 * // Get visits for a specific date
 * $visits = $instinct->getVisits('2024-12-13');
 * 
 * // List all accounts
 * $accounts = $instinct->getAccounts(['limit' => 50]);
 * 
 * // Get a specific account
 * $account = $instinct->getAccount('acc_123456789');
 * 
 * // List appointment types
 * $appointmentTypes = $instinct->getAppointmentTypes();
 * 
 * // List alerts with filtering
 * $alerts = $instinct->getAlerts(['status' => 'active']);
 */
class InstinctClass
{
    private $apiUrl;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $timeout;
    
    /**
     * Initialize the Instinct API client
     * 
     * @param string|null $apiUrl Optional API base URL (defaults to Instinct Partner API)
     * @param string|null $clientId Optional client ID (loads from .env if not provided)
     * @param string|null $clientSecret Optional client secret (loads from .env if not provided)
     * @param int $timeout Request timeout in seconds (default: 30)
     */
    public function __construct($apiUrl = null, $clientId = null, $clientSecret = null, $timeout = 30)
    {
        $this->apiUrl = $apiUrl ?: 'https://partner.instinctvet.com/v1/';
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->timeout = $timeout;
        $this->accessToken = null;
        
        // Load credentials from .env if not provided
        if (!$this->clientId || !$this->clientSecret) {
            $this->loadCredentialsFromEnv();
        }
    }
    
    // ==========================================
    // VISITS METHODS
    // ==========================================
    
    /**
     * Get visits for a specific date
     * 
     * Retrieves all veterinary visits that were checked in on the specified date.
     * Automatically converts simple date format to the required ISO 8601 format.
     * 
     * @param string $date Date in YYYY-MM-DD format (e.g., '2024-12-13')
     * @param array $params Optional additional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $visits = $instinct->getVisits('2024-12-13');
     * $visits = $instinct->getVisits('2024-12-13', ['limit' => 50]);
     */
    public function getVisits($date, $params = [])
    {
        $startOfDay = $date . 'T00:00:00.000000Z';
        $endOfDay = $date . 'T23:59:59.999999Z';
        
        $queryParams = array_merge([
            'checkedInSince' => $startOfDay,
            'checkedInBefore' => $endOfDay,
            'limit' => 100
        ], $params);
        
        return $this->get('/visits', $queryParams);
    }
    
    // ==========================================
    // ACCOUNTS METHODS
    // ==========================================
    
    /**
     * List all accounts with pagination
     * 
     * Retrieves a paginated list of accounts available in your Instinct system.
     * Use the pageCursor parameter to navigate through multiple pages of results.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $accounts = $instinct->getAccounts();
     * $accounts = $instinct->getAccounts(['limit' => 50]);
     * $nextPage = $instinct->getAccounts(['pageCursor' => 'cursor_string']);
     */
    public function getAccounts($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/accounts', $queryParams);
    }
    
    /**
     * Get a specific account by ID
     * 
     * Retrieves detailed information for a single account using its unique identifier.
     * 
     * @param string $accountId The unique identifier for the account
     * @return array|false API response array or false on failure
     * 
     * @example
     * $account = $instinct->getAccount('acc_123456789');
     */
    public function getAccount($accountId)
    {
        if (empty($accountId)) {
            return false;
        }
        
        return $this->get("/accounts/{$accountId}");
    }
    
    /**
     * Create a new account
     * 
     * Creates a new account in the Instinct system with the provided data.
     * 
     * @param array $accountData Account data (name, type, contact info, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $newAccount = $instinct->createAccount([
     *     'name' => 'Pet Clinic ABC',
     *     'type' => 'veterinary_clinic',
     *     'email' => 'contact@petclinicabc.com'
     * ]);
     */
    public function createAccount($accountData)
    {
        if (empty($accountData)) {
            return false;
        }
        
        return $this->post('/accounts', $accountData);
    }
    
    /**
     * Update an existing account
     * 
     * Updates specific fields of an existing account using PATCH method.
     * Only the provided fields will be updated, others remain unchanged.
     * 
     * @param string $accountId The unique identifier for the account
     * @param array $updateData Data to update (only include fields to be changed)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $updated = $instinct->updateAccount('acc_123456789', [
     *     'name' => 'Updated Pet Clinic Name',
     *     'email' => 'newemail@petclinic.com'
     * ]);
     */
    public function updateAccount($accountId, $updateData)
    {
        if (empty($accountId) || empty($updateData)) {
            return false;
        }
        
        return $this->patch("/accounts/{$accountId}", $updateData);
    }
    
    // ==========================================
    // APPOINTMENT TYPES METHODS
    // ==========================================
    
    /**
     * List all appointment types
     * 
     * Retrieves all available appointment types configured in your Instinct system.
     * These define the different types of appointments that can be scheduled.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $appointmentTypes = $instinct->getAppointmentTypes();
     * $appointmentTypes = $instinct->getAppointmentTypes(['limit' => 25]);
     */
    public function getAppointmentTypes($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/appointment-types', $queryParams);
    }
    
    /**
     * Get a specific appointment type by ID
     * 
     * Retrieves detailed information for a single appointment type using its unique identifier.
     * 
     * @param string $appointmentTypeId The unique identifier for the appointment type
     * @return array|false API response array or false on failure
     * 
     * @example
     * $appointmentType = $instinct->getAppointmentType('apt_123456789');
     */
    public function getAppointmentType($appointmentTypeId)
    {
        if (empty($appointmentTypeId)) {
            return false;
        }
        
        return $this->get("/appointment-types/{$appointmentTypeId}");
    }
    
    // ==========================================
    // ALERTS METHODS
    // ==========================================
    
    /**
     * List all alerts with pagination
     * 
     * Retrieves a paginated list of alerts from your Instinct system.
     * Alerts can include notifications, reminders, or system messages.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, status, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $alerts = $instinct->getAlerts();
     * $alerts = $instinct->getAlerts(['limit' => 50, 'status' => 'active']);
     */
    public function getAlerts($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/alerts', $queryParams);
    }
    
    /**
     * Get a specific alert by ID
     * 
     * Retrieves detailed information for a single alert using its unique identifier.
     * 
     * @param string $alertId The unique identifier for the alert
     * @return array|false API response array or false on failure
     * 
     * @example
     * $alert = $instinct->getAlert('alert_123456789');
     */
    public function getAlert($alertId)
    {
        if (empty($alertId)) {
            return false;
        }
        
        return $this->get("/alerts/{$alertId}");
    }
    
    // ==========================================
    // APPOINTMENTS METHODS
    // ==========================================
    
    /**
     * List all appointments with pagination and filtering
     * 
     * Retrieves a paginated list of appointments from your Instinct system.
     * Can be filtered by date range, status, location, and other parameters.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, startDate, endDate, status, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $appointments = $instinct->getAppointments();
     * $appointments = $instinct->getAppointments(['limit' => 50, 'status' => 'scheduled']);
     * $appointmentsToday = $instinct->getAppointments(['startDate' => '2024-12-13', 'endDate' => '2024-12-13']);
     */
    public function getAppointments($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/appointments', $queryParams);
    }
    
    /**
     * Get a specific appointment by ID
     * 
     * Retrieves detailed information for a single appointment using its unique identifier.
     * 
     * @param string $appointmentId The unique identifier for the appointment
     * @return array|false API response array or false on failure
     * 
     * @example
     * $appointment = $instinct->getAppointment('appt_123456789');
     */
    public function getAppointment($appointmentId)
    {
        if (empty($appointmentId)) {
            return false;
        }
        
        return $this->get("/appointments/{$appointmentId}");
    }
    
    /**
     * Update an existing appointment
     * 
     * Updates specific fields of an existing appointment using PATCH method.
     * Only the provided fields will be updated, others remain unchanged.
     * 
     * @param string $appointmentId The unique identifier for the appointment
     * @param array $updateData Data to update (status, time, notes, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $updated = $instinct->updateAppointment('appt_123456789', [
     *     'status' => 'confirmed',
     *     'notes' => 'Updated appointment notes'
     * ]);
     */
    public function updateAppointment($appointmentId, $updateData)
    {
        if (empty($appointmentId) || empty($updateData)) {
            return false;
        }
        
        return $this->patch("/appointments/{$appointmentId}", $updateData);
    }
    
    /**
     * Cancel an appointment
     * 
     * Cancels an appointment by setting its status to cancelled.
     * This is a POST operation that may include cancellation reason and notes.
     * 
     * @param string $appointmentId The unique identifier for the appointment
     * @param array $cancellationData Optional cancellation data (reason, notes, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $cancelled = $instinct->cancelAppointment('appt_123456789', [
     *     'reason' => 'patient_request',
     *     'notes' => 'Client requested to reschedule'
     * ]);
     */
    public function cancelAppointment($appointmentId, $cancellationData = [])
    {
        if (empty($appointmentId)) {
            return false;
        }
        
        return $this->post("/appointments/{$appointmentId}/cancel", $cancellationData);
    }
    
    // ==========================================
    // BREEDS METHODS
    // ==========================================
    
    /**
     * List all animal breeds
     * 
     * Retrieves a list of all animal breeds available in the Instinct system.
     * Useful for populating breed dropdowns in patient registration forms.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, species, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $breeds = $instinct->getBreeds();
     * $dogBreeds = $instinct->getBreeds(['species' => 'dog']);
     * $breeds = $instinct->getBreeds(['limit' => 200]);
     */
    public function getBreeds($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/breeds', $queryParams);
    }
    
    // ==========================================
    // DISPENSED PRESCRIPTIONS METHODS (BETA)
    // ==========================================
    
    /**
     * List all dispensed prescriptions
     * 
     * Retrieves a paginated list of dispensed prescriptions from your Instinct system.
     * Note: This is a beta feature and may have limited availability.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, startDate, endDate, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $prescriptions = $instinct->getDispensedPrescriptions();
     * $prescriptions = $instinct->getDispensedPrescriptions(['limit' => 50]);
     */
    public function getDispensedPrescriptions($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/dispensed-prescriptions', $queryParams);
    }
    
    /**
     * Get a specific dispensed prescription by ID
     * 
     * Retrieves detailed information for a single dispensed prescription.
     * 
     * @param string $prescriptionId The unique identifier for the dispensed prescription
     * @return array|false API response array or false on failure
     * 
     * @example
     * $prescription = $instinct->getDispensedPrescription('dp_123456789');
     */
    public function getDispensedPrescription($prescriptionId)
    {
        if (empty($prescriptionId)) {
            return false;
        }
        
        return $this->get("/dispensed-prescriptions/{$prescriptionId}");
    }
    
    // ==========================================
    // EXTERNAL PRESCRIPTIONS METHODS
    // ==========================================
    
    /**
     * List all external prescriptions
     * 
     * Retrieves a paginated list of external prescriptions (prescriptions filled outside the clinic).
     * 
     * @param array $params Optional query parameters (limit, pageCursor, startDate, endDate, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $externalPrescriptions = $instinct->getExternalPrescriptions();
     * $externalPrescriptions = $instinct->getExternalPrescriptions(['limit' => 25]);
     */
    public function getExternalPrescriptions($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/external-prescriptions', $queryParams);
    }
    
    /**
     * Get a specific external prescription by ID
     * 
     * Retrieves detailed information for a single external prescription.
     * 
     * @param string $prescriptionId The unique identifier for the external prescription
     * @return array|false API response array or false on failure
     * 
     * @example
     * $prescription = $instinct->getExternalPrescription('ep_123456789');
     */
    public function getExternalPrescription($prescriptionId)
    {
        if (empty($prescriptionId)) {
            return false;
        }
        
        return $this->get("/external-prescriptions/{$prescriptionId}");
    }
    
    // ==========================================
    // INVOICES METHODS
    // ==========================================
    
    /**
     * List all invoices with pagination and filtering
     * 
     * Retrieves a paginated list of invoices from your Instinct system.
     * Can be filtered by date range, status, payment status, and other parameters.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, startDate, endDate, status, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $invoices = $instinct->getInvoices();
     * $invoices = $instinct->getInvoices(['limit' => 50, 'status' => 'paid']);
     * $invoicesToday = $instinct->getInvoices(['startDate' => '2024-12-13']);
     */
    public function getInvoices($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/invoices', $queryParams);
    }
    
    /**
     * Get a specific invoice by ID
     * 
     * Retrieves detailed information for a single invoice including line items and payment history.
     * 
     * @param string $invoiceId The unique identifier for the invoice
     * @return array|false API response array or false on failure
     * 
     * @example
     * $invoice = $instinct->getInvoice('inv_123456789');
     */
    public function getInvoice($invoiceId)
    {
        if (empty($invoiceId)) {
            return false;
        }
        
        return $this->get("/invoices/{$invoiceId}");
    }
    
    /**
     * Create a new invoice
     * 
     * Creates a new invoice in the Instinct system with the provided data.
     * 
     * @param array $invoiceData Invoice data (patientId, visitId, lineItems, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $newInvoice = $instinct->createInvoice([
     *     'patientId' => 'pat_123456789',
     *     'visitId' => 'visit_123456789',
     *     'lineItems' => [
     *         ['description' => 'Consultation', 'amount' => 75.00]
     *     ]
     * ]);
     */
    public function createInvoice($invoiceData)
    {
        if (empty($invoiceData)) {
            return false;
        }
        
        return $this->post('/invoices', $invoiceData);
    }
    
    /**
     * Create a standalone invoice
     * 
     * Creates a standalone invoice that is not tied to a specific visit.
     * 
     * @param array $invoiceData Invoice data (patientId, lineItems, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $standaloneInvoice = $instinct->createStandaloneInvoice([
     *     'patientId' => 'pat_123456789',
     *     'lineItems' => [
     *         ['description' => 'Product Sale', 'amount' => 25.00]
     *     ]
     * ]);
     */
    public function createStandaloneInvoice($invoiceData)
    {
        if (empty($invoiceData)) {
            return false;
        }
        
        return $this->post('/invoices/standalone', $invoiceData);
    }
    
    // ==========================================
    // INVOICE LEDGER ENTRIES METHODS
    // ==========================================
    
    /**
     * List all invoice ledger entries
     * 
     * Retrieves a paginated list of invoice ledger entries showing payment history and adjustments.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, invoiceId, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $ledgerEntries = $instinct->getInvoiceLedgerEntries();
     * $invoiceLedger = $instinct->getInvoiceLedgerEntries(['invoiceId' => 'inv_123456789']);
     */
    public function getInvoiceLedgerEntries($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/invoice-ledger-entries', $queryParams);
    }
    
    // ==========================================
    // INVOICE LINE ITEMS METHODS
    // ==========================================
    
    /**
     * List invoice line items for a specific invoice
     * 
     * Retrieves a paginated list of line items for a specific invoice.
     * Requires an invoice ID as the endpoint is /invoices/{invoice_id}/line-items
     * 
     * @param string $invoiceId The unique identifier for the invoice
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $lineItems = $instinct->getInvoiceLineItems('inv_123456789');
     * $lineItems = $instinct->getInvoiceLineItems('inv_123456789', ['limit' => 50]);
     */
    public function getInvoiceLineItems($invoiceId, $params = [])
    {
        if (empty($invoiceId)) {
            return false;
        }
        
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get("/invoices/{$invoiceId}/line-items", $queryParams);
    }
    
    /**
     * Get a specific invoice line item by ID
     * 
     * Retrieves detailed information for a single invoice line item.
     * 
     * @param string $lineItemId The unique identifier for the invoice line item
     * @return array|false API response array or false on failure
     * 
     * @example
     * $lineItem = $instinct->getInvoiceLineItem('ili_123456789');
     */
    public function getInvoiceLineItem($lineItemId)
    {
        if (empty($lineItemId)) {
            return false;
        }
        
        return $this->get("/invoice-line-items/{$lineItemId}");
    }
    
    /**
     * Add a line item to an invoice
     * 
     * Adds a new line item to an existing invoice.
     * 
     * @param string $invoiceId The unique identifier for the invoice
     * @param array $lineItemData Line item data (description, amount, quantity, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $addedItem = $instinct->addInvoiceLineItem('inv_123456789', [
     *     'description' => 'Additional Service',
     *     'amount' => 50.00,
     *     'quantity' => 1
     * ]);
     */
    public function addInvoiceLineItem($invoiceId, $lineItemData)
    {
        if (empty($invoiceId) || empty($lineItemData)) {
            return false;
        }
        
        return $this->post("/invoices/{$invoiceId}/line-items", $lineItemData);
    }
    
    // ==========================================
    // LOCATIONS METHODS
    // ==========================================
    
    /**
     * List all locations
     * 
     * Retrieves a paginated list of all clinic locations in your Instinct system.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $locations = $instinct->getLocations();
     * $locations = $instinct->getLocations(['limit' => 25]);
     */
    public function getLocations($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/locations', $queryParams);
    }
    
    /**
     * Get a specific location by ID
     * 
     * Retrieves detailed information for a single location.
     * 
     * @param string $locationId The unique identifier for the location
     * @return array|false API response array or false on failure
     * 
     * @example
     * $location = $instinct->getLocation('loc_123456789');
     */
    public function getLocation($locationId)
    {
        if (empty($locationId)) {
            return false;
        }
        
        return $this->get("/locations/{$locationId}");
    }
    
    // ==========================================
    // ORDERS METHODS
    // ==========================================
    
    /**
     * Get a specific order by ID
     * 
     * Retrieves detailed information for a single order including treatments and products.
     * 
     * @param string $orderId The unique identifier for the order
     * @return array|false API response array or false on failure
     * 
     * @example
     * $order = $instinct->getOrder('ord_123456789');
     */
    public function getOrder($orderId)
    {
        if (empty($orderId)) {
            return false;
        }
        
        return $this->get("/orders/{$orderId}");
    }
    
    /**
     * List orders by visit
     * 
     * Retrieves all orders associated with a specific visit.
     * 
     * @param string $visitId The unique identifier for the visit
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $orders = $instinct->getOrdersByVisit('visit_123456789');
     * $orders = $instinct->getOrdersByVisit('visit_123456789', ['limit' => 10]);
     */
    public function getOrdersByVisit($visitId, $params = [])
    {
        if (empty($visitId)) {
            return false;
        }
        
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get("/visits/{$visitId}/orders", $queryParams);
    }
    
    // ==========================================
    // TREATMENTS METHODS
    // ==========================================
    
    /**
     * List treatments by order
     * 
     * Retrieves all treatments associated with a specific order.
     * 
     * @param string $orderId The unique identifier for the order
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $treatments = $instinct->getTreatmentsByOrder('ord_123456789');
     * $treatments = $instinct->getTreatmentsByOrder('ord_123456789', ['limit' => 20]);
     */
    public function getTreatmentsByOrder($orderId, $params = [])
    {
        if (empty($orderId)) {
            return false;
        }
        
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get("/orders/{$orderId}/treatments", $queryParams);
    }
    
    /**
     * Get a specific treatment by ID
     * 
     * Retrieves detailed information for a single treatment.
     * 
     * @param string $treatmentId The unique identifier for the treatment
     * @return array|false API response array or false on failure
     * 
     * @example
     * $treatment = $instinct->getTreatment('trt_123456789');
     */
    public function getTreatment($treatmentId)
    {
        if (empty($treatmentId)) {
            return false;
        }
        
        return $this->get("/treatments/{$treatmentId}");
    }
    
    // ==========================================
    // PATIENTS METHODS
    // ==========================================
    
    /**
     * List all patients with pagination and filtering
     * 
     * Retrieves a paginated list of patients from your Instinct system.
     * Can be filtered by various criteria like species, breed, client, etc.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, species, clientId, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $patients = $instinct->getPatients();
     * $patients = $instinct->getPatients(['limit' => 50, 'species' => 'dog']);
     * $clientPatients = $instinct->getPatients(['clientId' => 'client_123']);
     */
    public function getPatients($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/patients', $queryParams);
    }
    
    /**
     * Get a specific patient by ID
     * 
     * Retrieves detailed information for a single patient including medical history.
     * 
     * @param string $patientId The unique identifier for the patient
     * @return array|false API response array or false on failure
     * 
     * @example
     * $patient = $instinct->getPatient('pat_123456789');
     */
    public function getPatient($patientId)
    {
        if (empty($patientId)) {
            return false;
        }
        
        return $this->get("/patients/{$patientId}");
    }
    
    /**
     * Create a new patient
     * 
     * Creates a new patient record in the Instinct system.
     * 
     * @param array $patientData Patient data (name, species, breed, clientId, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $newPatient = $instinct->createPatient([
     *     'name' => 'Buddy',
     *     'species' => 'dog',
     *     'breed' => 'Golden Retriever',
     *     'clientId' => 'client_123456789'
     * ]);
     */
    public function createPatient($patientData)
    {
        if (empty($patientData)) {
            return false;
        }
        
        return $this->post('/patients', $patientData);
    }
    
    /**
     * Update an existing patient
     * 
     * Updates specific fields of an existing patient record.
     * 
     * @param string $patientId The unique identifier for the patient
     * @param array $updateData Data to update (name, weight, medical notes, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $updated = $instinct->updatePatient('pat_123456789', [
     *     'weight' => 65.5,
     *     'medicalNotes' => 'Updated medical history'
     * ]);
     */
    public function updatePatient($patientId, $updateData)
    {
        if (empty($patientId) || empty($updateData)) {
            return false;
        }
        
        return $this->patch("/patients/{$patientId}", $updateData);
    }
    
    /**
     * Transfer a patient to another client
     * 
     * Transfers ownership of a patient from one client to another.
     * 
     * @param string $patientId The unique identifier for the patient
     * @param array $transferData Transfer data (newClientId, reason, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $transferred = $instinct->transferPatient('pat_123456789', [
     *     'newClientId' => 'client_987654321',
     *     'reason' => 'Owner moved'
     * ]);
     */
    public function transferPatient($patientId, $transferData)
    {
        if (empty($patientId) || empty($transferData)) {
            return false;
        }
        
        return $this->post("/patients/{$patientId}/transfer", $transferData);
    }
    
    /**
     * Upload a file for a patient
     * 
     * Uploads a file (document, image, etc.) associated with a patient record.
     * 
     * @param string $patientId The unique identifier for the patient
     * @param array $fileData File upload data (file, description, type, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $uploaded = $instinct->uploadPatientFile('pat_123456789', [
     *     'file' => '/path/to/document.pdf',
     *     'description' => 'X-ray results',
     *     'type' => 'medical_record'
     * ]);
     */
    public function uploadPatientFile($patientId, $fileData)
    {
        if (empty($patientId) || empty($fileData)) {
            return false;
        }
        
        return $this->post("/patients/{$patientId}/files", $fileData);
    }
    
    // ==========================================
    // PAYMENT TRANSACTIONS METHODS
    // ==========================================
    
    /**
     * List all payment transactions
     * 
     * Retrieves a paginated list of payment transactions from your Instinct system.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, startDate, endDate, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $payments = $instinct->getPaymentTransactions();
     * $payments = $instinct->getPaymentTransactions(['limit' => 50]);
     */
    public function getPaymentTransactions($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/payments/transactions', $queryParams);
    }
    
    // ==========================================
    // PDF METHODS
    // ==========================================
    
    /**
     * Request a PDF generation
     * 
     * Requests the generation of a PDF document (invoice, report, etc.).
     * 
     * @param array $pdfData PDF request data (type, entityId, options, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $pdfRequest = $instinct->requestPdf([
     *     'type' => 'invoice',
     *     'entityId' => 'inv_123456789',
     *     'format' => 'A4'
     * ]);
     */
    public function requestPdf($pdfData)
    {
        if (empty($pdfData)) {
            return false;
        }
        
        return $this->post('/pdfs', $pdfData);
    }
    
    /**
     * Get PDF request status
     * 
     * Retrieves the status of a PDF generation request.
     * 
     * @param string $requestId The unique identifier for the PDF request
     * @return array|false API response array or false on failure
     * 
     * @example
     * $status = $instinct->getPdfRequestStatus('pdf_req_123456789');
     */
    public function getPdfRequestStatus($requestId)
    {
        if (empty($requestId)) {
            return false;
        }
        
        return $this->get("/pdfs/{$requestId}");
    }
    
    // ==========================================
    // PRODUCTS METHODS
    // ==========================================
    
    /**
     * List all products with pagination and filtering
     * 
     * Retrieves a paginated list of products from your Instinct system.
     * Can be filtered by category, availability, and other criteria.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, category, available, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $products = $instinct->getProducts();
     * $products = $instinct->getProducts(['limit' => 50, 'category' => 'medication']);
     * $availableProducts = $instinct->getProducts(['available' => true]);
     */
    public function getProducts($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/products', $queryParams);
    }
    
    /**
     * Get a specific product by ID
     * 
     * Retrieves detailed information for a single product including pricing and inventory.
     * 
     * @param string $productId The unique identifier for the product
     * @return array|false API response array or false on failure
     * 
     * @example
     * $product = $instinct->getProduct('prod_123456789');
     */
    public function getProduct($productId)
    {
        if (empty($productId)) {
            return false;
        }
        
        return $this->get("/products/{$productId}");
    }
    
    // ==========================================
    // REMINDER LABELS METHODS
    // ==========================================
    
    /**
     * List all reminder labels
     * 
     * Retrieves a list of all reminder labels used for categorizing reminders.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $reminderLabels = $instinct->getReminderLabels();
     * $reminderLabels = $instinct->getReminderLabels(['limit' => 25]);
     */
    public function getReminderLabels($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/reminder-labels', $queryParams);
    }
    
    /**
     * Get a specific reminder label by ID
     * 
     * Retrieves detailed information for a single reminder label.
     * 
     * @param string $labelId The unique identifier for the reminder label
     * @return array|false API response array or false on failure
     * 
     * @example
     * $label = $instinct->getReminderLabel('rl_123456789');
     */
    public function getReminderLabel($labelId)
    {
        if (empty($labelId)) {
            return false;
        }
        
        return $this->get("/reminder-labels/{$labelId}");
    }
    
    // ==========================================
    // REMINDERS METHODS
    // ==========================================
    
    /**
     * List all reminders with pagination and filtering
     * 
     * Retrieves a paginated list of reminders from your Instinct system.
     * Can be filtered by status, due date, patient, and other criteria.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, status, dueDate, patientId, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $reminders = $instinct->getReminders();
     * $reminders = $instinct->getReminders(['limit' => 50, 'status' => 'pending']);
     * $patientReminders = $instinct->getReminders(['patientId' => 'pat_123456789']);
     */
    public function getReminders($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/reminders', $queryParams);
    }
    
    /**
     * Get a specific reminder by ID
     * 
     * Retrieves detailed information for a single reminder.
     * 
     * @param string $reminderId The unique identifier for the reminder
     * @return array|false API response array or false on failure
     * 
     * @example
     * $reminder = $instinct->getReminder('rem_123456789');
     */
    public function getReminder($reminderId)
    {
        if (empty($reminderId)) {
            return false;
        }
        
        return $this->get("/reminders/{$reminderId}");
    }
    
    // ==========================================
    // SERVICES METHODS
    // ==========================================
    
    /**
     * List all services with pagination and filtering
     * 
     * Retrieves a paginated list of services available in your Instinct system.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, category, active, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $services = $instinct->getServices();
     * $services = $instinct->getServices(['limit' => 50, 'category' => 'surgery']);
     * $activeServices = $instinct->getServices(['active' => true]);
     */
    public function getServices($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/services', $queryParams);
    }
    
    /**
     * Get a specific service by ID
     * 
     * Retrieves detailed information for a single service including pricing and description.
     * 
     * @param string $serviceId The unique identifier for the service
     * @return array|false API response array or false on failure
     * 
     * @example
     * $service = $instinct->getService('svc_123456789');
     */
    public function getService($serviceId)
    {
        if (empty($serviceId)) {
            return false;
        }
        
        return $this->get("/services/{$serviceId}");
    }
    
    // ==========================================
    // USERS & TITLES METHODS
    // ==========================================
    
    /**
     * List all user titles
     * 
     * Retrieves a list of all user titles/roles available in the system.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $titles = $instinct->getTitles();
     * $titles = $instinct->getTitles(['limit' => 25]);
     */
    public function getTitles($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/titles', $queryParams);
    }
    
    /**
     * Get a specific title by ID
     * 
     * Retrieves detailed information for a single user title/role.
     * 
     * @param string $titleId The unique identifier for the title
     * @return array|false API response array or false on failure
     * 
     * @example
     * $title = $instinct->getTitle('title_123456789');
     */
    public function getTitle($titleId)
    {
        if (empty($titleId)) {
            return false;
        }
        
        return $this->get("/titles/{$titleId}");
    }
    
    /**
     * List all users with pagination and filtering
     * 
     * Retrieves a paginated list of users in your Instinct system.
     * Can be filtered by role, location, active status, etc.
     * 
     * @param array $params Optional query parameters (limit, pageCursor, role, locationId, active, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $users = $instinct->getUsers();
     * $users = $instinct->getUsers(['limit' => 50, 'role' => 'veterinarian']);
     * $activeUsers = $instinct->getUsers(['active' => true]);
     */
    public function getUsers($params = [])
    {
        $queryParams = array_merge([
            'limit' => 100
        ], $params);
        
        return $this->get('/users', $queryParams);
    }
    
    /**
     * Get a specific user by ID
     * 
     * Retrieves detailed information for a single user including permissions and contact info.
     * 
     * @param string $userId The unique identifier for the user
     * @return array|false API response array or false on failure
     * 
     * @example
     * $user = $instinct->getUser('user_123456789');
     */
    public function getUser($userId)
    {
        if (empty($userId)) {
            return false;
        }
        
        return $this->get("/users/{$userId}");
    }
    
    // ==========================================
    // ADDITIONAL VISITS METHODS
    // ==========================================
    
    /**
     * Create a new visit
     * 
     * Creates a new visit record in the Instinct system.
     * 
     * @param array $visitData Visit data (patientId, appointmentId, reason, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $newVisit = $instinct->createVisit([
     *     'patientId' => 'pat_123456789',
     *     'reason' => 'Annual checkup',
     *     'appointmentId' => 'appt_123456789'
     * ]);
     */
    public function createVisit($visitData)
    {
        if (empty($visitData)) {
            return false;
        }
        
        return $this->post('/visits', $visitData);
    }
    
    /**
     * Update an existing visit
     * 
     * Updates specific fields of an existing visit record.
     * 
     * @param string $visitId The unique identifier for the visit
     * @param array $updateData Data to update (status, notes, diagnosis, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $updated = $instinct->updateVisit('visit_123456789', [
     *     'status' => 'completed',
     *     'diagnosis' => 'Healthy - routine checkup',
     *     'notes' => 'Patient is in good health'
     * ]);
     */
    public function updateVisit($visitId, $updateData)
    {
        if (empty($visitId) || empty($updateData)) {
            return false;
        }
        
        return $this->patch("/visits/{$visitId}", $updateData);
    }
    
    /**
     * Check out a visit
     * 
     * Marks a visit as checked out and completes the visit process.
     * 
     * @param string $visitId The unique identifier for the visit
     * @param array $checkoutData Optional checkout data (payment info, follow-up notes, etc.)
     * @return array|false API response array or false on failure
     * 
     * @example
     * $checkedOut = $instinct->checkOutVisit('visit_123456789', [
     *     'paymentMethod' => 'credit_card',
     *     'followUpRequired' => false
     * ]);
     */
    public function checkOutVisit($visitId, $checkoutData = [])
    {
        if (empty($visitId)) {
            return false;
        }
        
        return $this->post("/visits/{$visitId}/check-out", $checkoutData);
    }
    
    /**
     * Make a GET request to the API
     * 
     * @param string $endpoint API endpoint (e.g., '/visits', '/patients')
     * @param array $params Optional query parameters
     * @return array|false API response array or false on failure
     */
    public function get($endpoint, $params = [])
    {
        if (!$this->accessToken && !$this->authenticate()) {
            return false;
        }
        
        $url = $this->buildUrl($endpoint, $params);
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Make a POST request to the API
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array|false API response array or false on failure
     */
    public function post($endpoint, $data = [])
    {
        if (!$this->accessToken && !$this->authenticate()) {
            return false;
        }
        
        return $this->makeRequest('POST', $this->buildUrl($endpoint), $data);
    }
    
    /**
     * Make a PUT request to the API
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array|false API response array or false on failure
     */
    public function put($endpoint, $data = [])
    {
        if (!$this->accessToken && !$this->authenticate()) {
            return false;
        }
        
        return $this->makeRequest('PUT', $this->buildUrl($endpoint), $data);
    }
    
    /**
     * Make a PATCH request to the API
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array|false API response array or false on failure
     */
    public function patch($endpoint, $data = [])
    {
        if (!$this->accessToken && !$this->authenticate()) {
            return false;
        }
        
        return $this->makeRequest('PATCH', $this->buildUrl($endpoint), $data);
    }
    
    /**
     * Make a DELETE request to the API
     * 
     * @param string $endpoint API endpoint
     * @return array|false API response array or false on failure
     */
    public function delete($endpoint)
    {
        if (!$this->accessToken && !$this->authenticate()) {
            return false;
        }
        
        return $this->makeRequest('DELETE', $this->buildUrl($endpoint));
    }
    
    /**
     * Get the current API base URL
     * 
     * @return string API base URL
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }
    
    /**
     * Get the current access token
     * 
     * @return string|null Current access token or null if not authenticated
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    
    /**
     * Load API credentials from environment file
     * 
     * Attempts to load INSTINCT_CLIENT_ID and INSTINCT_CLIENT_SECRET from .env file
     * 
     * @return void
     */
    private function loadCredentialsFromEnv()
    {
        try {
            $dotenv = Dotenv::create(dirname(dirname(dirname(__FILE__))));
            $dotenv->load();
            
            $this->clientId = getenv('INSTINCT_CLIENT_ID');
            $this->clientSecret = getenv('INSTINCT_CLIENT_SECRET');
        } catch (\Exception $e) {
            error_log("InstinctClass: Failed to load environment variables - " . $e->getMessage());
        }
    }
    
    /**
     * Authenticate with the Instinct API using OAuth2 client credentials
     * 
     * @return bool True on successful authentication, false otherwise
     */
    private function authenticate()
    {
        if (!$this->clientId || !$this->clientSecret) {
            error_log("InstinctClass: Missing client credentials");
            return false;
        }
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => rtrim($this->apiUrl, '/') . '/auth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials'
            ]),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Build complete URL for API endpoint
     * 
     * @param string $endpoint API endpoint
     * @param array $params Optional query parameters
     * @return string Complete URL
     */
    private function buildUrl($endpoint, $params = [])
    {
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Execute HTTP request to API
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $url Complete URL
     * @param array|null $data Request body data for POST/PUT requests
     * @return array|false API response array or false on failure
     */
    private function makeRequest($method, $url, $data = null)
    {
        $curl = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->accessToken) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return false;
        }
        
        return [
            'http_code' => $httpCode,
            'data' => json_decode($response, true),
            'raw_response' => $response,
            'success' => $httpCode >= 200 && $httpCode < 300
        ];
    }
} 