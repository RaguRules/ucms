<?php

class Helper {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function generateNextRegistrationID() {
        return $this->generateNextID('registration', 'reg_id', 'R', 4);
    }

    public function generateNextLawyerID() {
        return $this->generateNextID('lawyer', 'lawyer_id', 'L', 4);
    }

    public function generateNextPoliceID() {
        return $this->generateNextID('police', 'police_id', 'P', 4);
    }

    public function generateNextRoleID() {
        return $this->generateNextID('roles', 'role_id', 'R', 2);
    }

    public function generateNextStaffID() {
        return $this->generateNextID('staff', 'staff_id', 'S', 4);
    }

    public function generateNextPartyID() {
        return $this->generateNextID('parties', 'party_id', 'P', 4);
    }

    private function generateNextID($table, $column, $prefix, $padLength) {
        $sql = "SELECT MAX($column) AS max_id FROM $table";
        $result = mysqli_query($this->conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $max_id = $row['max_id'];

            if ($max_id === null) {
                return $prefix . str_pad("1", $padLength, "0", STR_PAD_LEFT);
            } else {
                $numeric_part = (int)substr($max_id, 1);
                $next_numeric_part = $numeric_part + 1;
                return $prefix . str_pad($next_numeric_part, $padLength, "0", STR_PAD_LEFT);
            }
        } else {
            return $prefix . str_pad("1", $padLength, "0", STR_PAD_LEFT);
        }
    }

    public function getStaffData($staff_id) {
        $stmt = $this->conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
        $stmt->bind_param("s", $staff_id);
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

    public function getPartyData($party_id) {
        $stmt = $this->conn->prepare("SELECT * FROM parties WHERE party_id = ?");
        $stmt->bind_param("s", $party_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    public function getCourtName($court_id) {
        $courts = [
            'C01' => "Magistrate's Court",
            'C02' => "District Court",
            'C03' => "High Court"
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

}