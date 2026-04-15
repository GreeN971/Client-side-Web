<?php
require_once __DIR__ . '/classes/dbh.classes.php';

class Seeder extends Dbh {
    public function run(): void {
        $db = $this->connect();
        $this->seedUsers($db);
        $this->seedCheckins($db);
        $this->seedDemoCheckins($db);
        $this->seedDemoSessions($db);
        echo "Done.\n";
    }

    // ── Real users ────────────────────────────────────────────────────────────

    private function seedUsers(PDO $db): void {
        $users = [
            ['alice',   'password123', 'alice@example.com',   'Whiskers'],
            ['bob',     'password123', 'bob@example.com',     'Rex'],
            ['charlie', 'password123', 'charlie@example.com', 'Buddy'],
            ['diana',   'password123', 'diana@example.com',   'Luna'],
            ['eve',     'password123', 'eve@example.com',     'Mittens'],
            ['frank',   'password123', 'frank@example.com',   'Max'],
            ['grace',   'password123', 'grace@example.com',   'Bella'],
            ['henry',   'password123', 'henry@example.com',   'Charlie'],
            ['iris',    'password123', 'iris@example.com',    'Daisy'],
            ['jack',    'password123', 'jack@example.com',    'Rocky'],
        ];

        $insert = $db->prepare(
            'INSERT IGNORE INTO users (username, users_pwd, users_email, pets_name)
             VALUES (?, ?, ?, ?)'
        );

        foreach ($users as [$username, $pwd, $email, $pet]) {
            $hashed = password_hash($pwd, PASSWORD_DEFAULT);
            $insert->execute([$username, $hashed, $email, $pet]);
            echo "User: $username\n";
        }
    }

    // ── Real check-ins ────────────────────────────────────────────────────────

    private function seedCheckins(PDO $db): void {
        $emotions = ['joy', 'sadness', 'anger', 'calmness', 'neutral', 'anxiety'];
        $notes    = [
            'Feeling great today!',
            'Had a rough day.',
            'Pretty frustrated with things.',
            'Very relaxed evening.',
            'Nothing special.',
            'A bit stressed about work.',
            '',
        ];

        // Fetch real user IDs (non-admin)
        $rows = $db->query('SELECT users_id FROM users WHERE is_admin = 0')->fetchAll(PDO::FETCH_COLUMN);

        $insert = $db->prepare(
            'INSERT INTO checkins (user_id, emotion, note, created_at) VALUES (?, ?, ?, ?)'
        );

        // Generate ~5 check-ins per user over the last 30 days
        foreach ($rows as $userId) {
            $count = rand(3, 7);
            $usedDays = [];
            for ($i = 0; $i < $count; $i++) {
                // Pick a random day in the last 30 days, avoid exact duplicates per user
                do {
                    $daysAgo = rand(0, 29);
                } while (in_array($daysAgo, $usedDays, true));
                $usedDays[] = $daysAgo;

                $ts      = date('Y-m-d H:i:s', strtotime("-$daysAgo days") - rand(0, 50000));
                $emotion = $emotions[array_rand($emotions)];
                $note    = $notes[array_rand($notes)];

                $insert->execute([$userId, $emotion, $note, $ts]);
            }
            echo "Check-ins seeded for user_id $userId\n";
        }
    }

    // ── Demo check-ins ────────────────────────────────────────────────────────

    private function seedDemoCheckins(PDO $db): void {
        $db->exec('TRUNCATE TABLE demo_checkins');

        $emotions   = ['joy', 'sadness', 'anger', 'calmness', 'neutral', 'anxiety'];
        $userCount  = 20;   // number of fake users
        $daysBack   = 30;

        $insert = $db->prepare(
            'INSERT INTO demo_checkins (fake_user_id, emotion, created_at) VALUES (?, ?, ?)'
        );

        for ($uid = 1; $uid <= $userCount; $uid++) {
            $checkinDays = (array) array_rand(range(0, $daysBack - 1), rand(5, 15));
            foreach ($checkinDays as $daysAgo) {
                $ts      = date('Y-m-d H:i:s', strtotime("-$daysAgo days") - rand(0, 50000));
                $emotion = $emotions[array_rand($emotions)];
                $insert->execute([$uid, $emotion, $ts]);
            }
        }

        echo "demo_checkins seeded ($userCount fake users, ~5-15 check-ins each over $daysBack days)\n";
    }

    // ── Demo sessions (online users) ──────────────────────────────────────────

    private function seedDemoSessions(PDO $db): void {
        $db->exec('TRUNCATE TABLE demo_sessions');

        $fakeNames = ['demo_alice', 'demo_bob', 'demo_carol', 'demo_dave', 'demo_eve'];

        $insert = $db->prepare(
            'INSERT INTO demo_sessions (fake_username, last_active) VALUES (?, NOW())'
        );

        foreach ($fakeNames as $name) {
            $insert->execute([$name]);
        }

        echo 'demo_sessions seeded (' . count($fakeNames) . " fake online users)\n";
    }
}

(new Seeder())->run();
