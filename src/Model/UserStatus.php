<?php

class UserStatus
{
    private ?PDO $conn = null;

    public function __construct()
    {
        try {
            $this->conn = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            logWithDate('DB connection failed', $e->getMessage());
        }
    }

    public function setOnline(int $userId): void
    {
        try {
            throwDbNullConnection($this->conn);

            $query = 'INSERT INTO user_status (user_id, is_online, is_in_game, updated_at, current_game_id) ';
            $query .= 'VALUES (:user_id, 1, 0, NOW(), NULL) ';
            $query .= 'ON DUPLICATE KEY UPDATE is_online = VALUES(is_online), is_in_game = 0, current_game_id = NULL, updated_at = VALUES(updated_at)';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException|Exception $e) {
            logWithDate('Query failed', $e->getMessage());
        }
    }

    public function setOffline(int $userId): void
    {
        try {
            throwDbNullConnection($this->conn);

            $query = 'UPDATE user_status SET is_online = 0, is_in_game = 0, current_game_id = NULL, updated_at = NOW() WHERE user_id = :user_id';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException|Exception $e) {
            logWithDate('Query failed', $e->getMessage());
        }
    }

    public function setInGame(int $userId, int $gameId): void
    {
        try {
            throwDbNullConnection($this->conn);

            $query = 'INSERT INTO user_status (user_id, is_online, is_in_game, current_game_id, updated_at) ';
            $query .= 'VALUES (:user_id, 1, 1, :game_id, NOW()) ';
            $query .= 'ON DUPLICATE KEY UPDATE is_online = 1, is_in_game = 1, current_game_id = VALUES(current_game_id), updated_at = VALUES(updated_at)';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':game_id', $gameId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException|Exception $e) {
            logWithDate('Query failed', $e->getMessage());
        }
    }

    public function clearGame(int $userId): void
    {
        try {
            throwDbNullConnection($this->conn);

            $query = 'UPDATE user_status SET is_in_game = 0, current_game_id = NULL, updated_at = NOW() WHERE user_id = :user_id';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException|Exception $e) {
            logWithDate('Query failed', $e->getMessage());
        }
    }

    public function listAvailableOpponents(int $excludeUserId): array
    {
        try {
            throwDbNullConnection($this->conn);

            $query = 'SELECT u.id, u.pseudo, u.avatar ';
            $query .= 'FROM user_status us ';
            $query .= 'JOIN user u ON u.id = us.user_id ';
            $query .= 'WHERE us.is_online = 1 AND us.is_in_game = 0 AND u.id <> :user_id ';
            $query .= 'ORDER BY u.pseudo ASC';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $excludeUserId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException|Exception $e) {
            logWithDate('Query failed', $e->getMessage());
            return [];
        }
    }

    public function isUserAvailable(int $userId): bool
    {
        try {
            throwDbNullConnection($this->conn);

            $query = 'SELECT is_online, is_in_game FROM user_status WHERE user_id = :user_id';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch();
            if (!$result) {
                return false;
            }

            return (int)$result['is_online'] === 1 && (int)$result['is_in_game'] === 0;
        } catch (PDOException|Exception $e) {
            logWithDate('Query failed', $e->getMessage());
            return false;
        }
    }
}