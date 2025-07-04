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


    // private function generateNextID($table, $column, $prefix, $padLength) {
    //     $sql = "SELECT MAX($column) AS max_id FROM $table";
    //     $result = mysqli_query($this->conn, $sql);

    //     if ($result && mysqli_num_rows($result) > 0) {
    //         $row = mysqli_fetch_assoc($result);
    //         $max_id = $row['max_id'];

    //         if ($max_id === null) {
    //             return $prefix . str_pad("1", $padLength, "0", STR_PAD_LEFT);
    //         } else {
    //             $numeric_part = (int)substr($max_id, 1);
    //             $next_numeric_part = $numeric_part + 1;
    //             return $prefix . str_pad($next_numeric_part, $padLength, "0", STR_PAD_LEFT);
    //         }
    //     } else {
    //         return $prefix . str_pad("1", $padLength, "0", STR_PAD_LEFT);
    //     }
    // }

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
}