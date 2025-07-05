<?php

class Helper {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function generateNextRegistrationID() {
        return $this->generateNextID('registration', 'reg_id', 'R', 8);
    }

    public function generateNextLawyerID() {
        return $this->generateNextID('lawyer', 'lawyer_id', 'L', 8);
    }

    public function generateNextPoliceID() {
        return $this->generateNextID('police', 'police_id', 'P', 8);
    }

    public function generateNextRoleID() {
        return $this->generateNextID('roles', 'role_id', 'R', 2);
    }

    public function generateNextStaffID() {
        return $this->generateNextID('staff', 'staff_id', 'S', 8);
    }

    public function generateNextPartyID() {
        return $this->generateNextID('parties', 'party_id', 'P', 8);
    }

    public function generateNextCaseID() {
        return $this->generateNextID('cases', 'case_id', 'C', 8);
    }

    public function generateNextCourtID() {
        return $this->generateNextID('courts', 'court_id', 'C', 2);
    }

    public function generateNextActivityID() {
        return $this->generateNextID('dailycaseactivities', 'activity_id', 'A', 8);
    }

    public function generateNextAppealID() {
        return $this->generateNextID('appeals', 'appeal_id', 'A', 8);
    }

    public function generateNextJudgementID() {
        return $this->generateNextID('judgements', 'jud_id', 'J', 8);
    }

    public function generateNextOrderID() {
        return $this->generateNextID('orders', 'order_id', 'O', 8);
    }

    public function generateNextMotionID() {
        return $this->generateNextID('orders', 'order_id', 'O', 8);
    }

    public function generateNextNotesID() {
        return $this->generateNextID('notes', 'note_id', 'N', 8);
    }

    public function generateNextNotificationID() {
        return $this->generateNextID('notifications', 'notification_id', 'N', 8);
    }

    private function generateNextID($table, $column, $prefix, $padLength) {
        $sql = "SELECT $column FROM $table WHERE $column LIKE '$prefix%' ORDER BY CAST(SUBSTRING($column, 2) AS UNSIGNED) DESC LIMIT 1";
        $result = mysqli_query($this->conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $max_id = $row[$column];

            // Extract numeric part safely
            $numeric_part = (int)substr($max_id, 1);
            $next_numeric_part = $numeric_part + 1;

            return $prefix . str_pad($next_numeric_part, $padLength, "0", STR_PAD_LEFT);
        } else {
            return $prefix . str_pad("1", $padLength, "0", STR_PAD_LEFT);
        }
    }


    public function getStaffData($staffId) {
        $stmt = $this->conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
        $stmt->bind_param("s", $staffId);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getLawyerData($lawyer_id) {
        $stmt = $this->conn->prepare("SELECT * FROM lawyer WHERE lawyer_id = ?");
        $stmt->bind_param("s", $lawyer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getPoliceData($police_id) {
        $stmt = $this->conn->prepare("SELECT * FROM police WHERE police_id = ?");
        $stmt->bind_param("s", $police_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getPartyData($party_id) {
        $stmt = $this->conn->prepare("SELECT * FROM parties WHERE party_id = ?");
        $stmt->bind_param("s", $party_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getCaseData($case_id) {
        $stmt = $this->conn->prepare("SELECT * FROM cases WHERE case_id = ?");
        $stmt->bind_param("s", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getActivityData($activity_id) {
        $stmt = $this->conn->prepare("SELECT * FROM dailycaseactivities WHERE activity_id = ?");
        $stmt->bind_param("s", $activity_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getAllStaff() {
        $stmt = $this->conn->prepare("SELECT * FROM staff");
        $stmt->execute();
        return $stmt->get_result(); // Return result set
    }


    public function getAllLawyers() {
        $stmt = $this->conn->prepare("SELECT * FROM lawyer");
        $stmt->execute();
        return $stmt->get_result();
    }

   
    public function getAllPolice() {
        $stmt = $this->conn->prepare("SELECT * FROM police");
        $stmt->execute();
        return $stmt->get_result();
    }


    public function getAllParties() {
    $stmt = $this->conn->prepare("SELECT * FROM parties");
    $stmt->execute();
    return $stmt->get_result();
    }


    public function getCourtName($court_id) {
        $courts = [
            'C01' => "Magistrate's Court",
            'C02' => "District Court",
            'C03' => "High Court",
            'C04' => "Juvenile Magistrate's Court"
            
        ];
        return $courts[$court_id] ?? "Unknown";
    }

    public function getRoleName($role_id) {
        $roles = [
            'R01' => "Administrator",
            'R02' => "Hon. Judge",
            'R03' => "The Registrar",
            'R04' => "Interpreter",
            'R05' => "Common Staff",
            'R06' => "Lawyer",
            'R07' => "Police"
        ];
        return $roles[$role_id] ?? "Unknown";
    }

    public function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function getId($username, $type) {
        switch ($type) {
            case 'R01':
            case 'R02':
            case 'R03':
            case 'R04':
            case 'R05':
                $table = 'staff';
                $idColumn = 'staff_id';
                break;
            case 'R06':
                $table = 'lawyer';
                $idColumn = 'lawyer_id';
                break;
            case 'R07':
                $table = 'police';
                $idColumn = 'police_id';
                break;
            default:
                return null; // Unknown role
        }

        // Prepare and execute query
        $stmt = $this->conn->prepare("SELECT $idColumn FROM $table WHERE email = ?");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch and return the ID
        if ($row = $result->fetch_assoc()) {
            return $row[$idColumn];
        }

        return null; // No matching user found
    }


    public function hasArrestWarrant($caseId) {
        $stmt = $this->conn->prepare("SELECT is_warrant FROM cases WHERE case_id = ?");
        $stmt->bind_param("s", $caseId);
        $stmt->execute();
        $stmt->bind_result($isWarrent);
        if ($stmt->fetch()) {
            return $isWarrent == 1;
        }
        return false; // Case not found or no warrant
    }




    // -----------------------------------------------------
    // ------- Notification related Helper Functions -------
    // -----------------------------------------------------


    public function insertNotification($record_id, $type, $court_id, $message, $receiver_id) {
        $sql = "INSERT INTO notifications (notification_id, record_id, type, court_id, status, message, receiver_id) VALUES (?, ?, ?, ?, 'unread', ?, ?)";
        $notification_id = $this->generateNextNotificationId(); // existing ID generator function

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $notification_id, $record_id, $type, $court_id, $message, $receiver_id);
        $stmt->execute();
    }


    public function getStaffIdByRoleAndCourt($role_id, $court_id) {
        $stmt = $this->conn->prepare("SELECT staff_id FROM staff WHERE role_id = ? AND court_id = ? LIMIT 1");
        $stmt->bind_param("ss", $role_id, $court_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['staff_id'] : null;
    }


    public function triggerJudgementNotification($case_id) {
        $case = $this->getCaseData($case_id);
        if (!$case) return;

        $court_id = $case['court_id'] ?? null;
        $message = "Judgement issued for case {$case_id} - {$case['case_name']}.";

        // Assume you have stored or can get Registrar staff ID by querying staff where role_id = 'R03'
        $registrarId = $this->getStaffIdByRoleAndCourt('R03', $court_id);
        if ($registrarId) {
            $this->insertNotification($case_id, 'Judgement', $court_id, $message, $registrarId);
        }

        $interpreterId = $this->getStaffIdByRoleAndCourt('R04', $court_id);
        if ($interpreterId) {
            $this->insertNotification($case_id, 'Judgement', $court_id, $message, $interpreterId);
        }

        if (!empty($case['plaintiff_lawyer'])) {
            $lawyer = $this->getLawyerData($case['plaintiff_lawyer']);
            if ($lawyer) {
                $this->insertNotification($case_id, 'Judgement', $court_id, $message, $lawyer['lawyer_id']);
            }
        }

        if (!empty($case['defendant_lawyer'])) {
            $lawyer = $this->getLawyerData($case['defendant_lawyer']);
            if ($lawyer) {
                $this->insertNotification($case_id, 'Judgement', $court_id, $message, $lawyer['lawyer_id']);
            }
        }
    }


    public function triggerOrderNotification($case_id) {
        $case = $this->getCaseData($case_id);
        if (!$case){
            return;
        }

        if (!is_string($receiver_id)) {
            error_log("⚠️ insertNotification: receiver_id is not string! " . print_r($receiver_id, true));
            return;
        }

        $court_id = $case['court_id'] ?? null;
        $message = "Order issued for case {$case_id} - {$case['case_name']}.";

        // Notify Registrar (role_id = 'R03')
        $registrarId = $this->getStaffIdByRoleAndCourt('R03', $court_id);
        if ($registrarId) {
            $this->insertNotification($case_id, 'Order', $court_id, $message, $registrarId);
        }

        // Notify Interpreter (role_id = 'R04')
        $interpreterId = $this->getStaffIdByRoleAndCourt('R04', $court_id);
        if ($interpreterId) {
            $this->insertNotification($case_id, 'Order', $court_id, $message, $interpreterId);
        }

        // Notify Plaintiff's Lawyer
        if (!empty($case['plaintiff_lawyer'])) {
            $lawyer = $this->getLawyerData($case['plaintiff_lawyer']);
            if ($lawyer) {
                $this->insertNotification($case_id, 'Order', $court_id, $message, $lawyer['lawyer_id']);
            }
        }

        // Notify Defendant's Lawyer
        if (!empty($case['defendant_lawyer'])) {
            $lawyer = $this->getLawyerData($case['defendant_lawyer']);
            if ($lawyer) {
                $this->insertNotification($case_id, 'Order', $court_id, $message, $lawyer['lawyer_id']);
            }
        }
}


    public function getCaseLawyers($caseId) {
        $lawyers = [];

        $sql = "
            SELECT l.lawyer_id, l.first_name, l.last_name, l.email, l.mobile, l.staff_id, 
                'Plaintiff' AS role
            FROM cases c
            LEFT JOIN lawyer l ON l.lawyer_id = c.plaintiff_lawyer
            WHERE c.case_id = ?
            UNION
            SELECT l2.lawyer_id, l2.first_name, l2.last_name, l2.email, l2.mobile, l2.staff_id, 
                'Defendant' AS role
            FROM cases c2
            LEFT JOIN lawyer l2 ON l2.lawyer_id = c2.defendant_lawyer
            WHERE c2.case_id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $caseId, $caseId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $lawyers[] = $row;
        }

        return $lawyers; // returns an array of lawyers with their role (Plaintiff or Defendant)
    }

    public function getInterpreterByCourt($courtId) {
        $sql = "SELECT * FROM staff WHERE court_id = ? AND appointment = 'Interpreter' AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $courtId);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }


    public function getJudgeByCourt($courtId) {
        $stmt = $this->conn->prepare("SELECT staff_id FROM staff WHERE court_id = ? AND role_id = 'R02' AND is_active = 1");
        $stmt->bind_param("s", $courtId);
        $stmt->execute();
        $result = $stmt->get_result();

        $judges = [];
        while ($row = $result->fetch_assoc()) {
            $judges[] = $row['staff_id'];
        }

        return $judges; // array, even if 1 judge
    }


    public function getRegistrarByCourt($courtId) {
        $stmt = $this->conn->prepare("SELECT staff_id FROM staff WHERE court_id = ? AND role_id = 'R03' AND is_active = 1");
        $stmt->bind_param("s", $courtId);
        $stmt->execute();
        $result = $stmt->get_result();

        $registrars = [];
        while ($row = $result->fetch_assoc()) {
            $registrars[] = $row['staff_id'];
        }

        return $registrars; // array of staff_id(s)
    }



    public function triggerNextDateUpdated($caseId) {
        // Get case details
        $caseData = $this->getCaseData($caseId);
        if (!$caseData) return;

        $courtId = $caseData['court_id'];
        
        // Get all assigned lawyers for this case (plaintiff and defendant)
        $lawyers = $this->getCaseLawyers($caseId); // You should have this helper that returns array of lawyer staff_ids
        
        // Get Interpreter for this court
        $interpreters = $this->getInterpreterByCourt($courtId); // Should return staff_id or null
        
        // Prepare message
        $message = "Next hearing date has been updated for case '{$caseData['case_name']}'. Please check the new schedule.";
        
        // Insert notifications for lawyers
      foreach ($lawyers as $lawyer) {
        if (!empty($lawyer['email'])) {
            error_log("Inserting notification for lawyer email: " . $lawyer['email']);
            $this->insertNotification($caseId, 'next_date_updated', $courtId, $message, $lawyer['email']);
        } else {
            $this->insertNotification($caseId, 'next_date_updated', $courtId, $message, 'cannot retrieve lawyer email');
            error_log("Lawyer email is empty for case $caseId");
        }
    }

        
        // Insert notification for interpreter if exists
        if (!empty($interpreters)) {
            foreach ($interpreters as $interpreter) {
                $this->insertNotification($caseId, 'next_date_updated', $courtId, $message, $interpreter['email']);
            }
        }

    }




}
