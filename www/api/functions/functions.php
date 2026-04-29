<?php
// Allow requests from any origin (use specific origin in production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// functions.php
require_once __DIR__ . '/../vendor/autoload.php';

// CREATE 
function createUser($data) {
    try {
        $password = password_hash($data['password'] ?? '', PASSWORD_BCRYPT);

        $user = ORM::for_table('users_table')->create();
        $user->username  = $data['username']  ?? '';
        $user->full_name = $data['full_name'] ?? '';
        $user->nickname  = $data['nickname']  ?? '';
        $user->address   = $data['address']   ?? '';
        $user->birthday  = $data['birthday']  ?? '';
        $user->age       = $data['age']       ?? '';
        $user->contact   = $data['contact']   ?? '';
        $user->email     = $data['email']     ?? '';
        $user->password  = $password;
        $user->save();

        return ["success" => true, "message" => "Account created!"];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// GET ALL
function getAllUsers() {
    try {
        $users = ORM::for_table('users_table')
            ->select_many('username', 'full_name', 'nickname', 'address', 'birthday', 'age', 'contact', 'email')
            ->find_array();

        return ["success" => true, "users" => $users];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// GET ONE
function getUserByUsername($username) {
    try {
        $user = ORM::for_table('users_table')
            ->where('username', $username)
            ->find_one();

        if ($user) {
            return ["success" => true, "user" => $user->as_array()];
        }

        return ["success" => false, "message" => "User not found."];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// UPDATE
function updateUser($data) {
    try {
        $user = ORM::for_table('users_table')
            ->where('username', $data['original_username'] ?? '')
            ->find_one();

        if (!$user) {
            return ["success" => false, "message" => "User not found."];
        }

        $user->username  = $data['username']  ?? $user->username;
        $user->full_name = $data['full_name'] ?? $user->full_name;
        $user->nickname  = $data['nickname']  ?? $user->nickname;
        $user->address   = $data['address']   ?? $user->address;
        $user->birthday  = $data['birthday']  ?? $user->birthday;
        $user->age       = $data['age']       ?? $user->age;
        $user->contact   = $data['contact']   ?? $user->contact;
        $user->email     = $data['email']     ?? $user->email;

        if (!empty($data['new_password'])) {
            if (strlen($data['new_password']) < 8) {
                return ["success" => false, "message" => "Password must be at least 8 characters."];
            }
            $user->password = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        $user->save();

        $updated = $user->as_array();
        unset($updated['password']);

        return ["success" => true, "user" => $updated];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// ── DELETE
function deleteUser($username) {
    try {
        $user = ORM::for_table('users_table')
            ->where('username', $username)
            ->find_one();

        if (!$user) {
            return ["success" => false, "message" => "User not found."];
        }
        $user->delete();
        return ["success" => true, "message" => "User deleted."];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// ── LOGIN ──
function loginUser($username, $password) {
    try {
        $user = ORM::for_table('users_table')
            ->where('username', $username)
            ->find_one();

        if (!$user) {
            return ["success" => false, "message" => "User not found."];
        }

        if (!password_verify($password, $user->password)) {
            return ["success" => false, "message" => "Invalid credentials."];
        }

        $data = $user->as_array();
        unset($data['password']);

        return ["success" => true, "user" => $data];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}
?>