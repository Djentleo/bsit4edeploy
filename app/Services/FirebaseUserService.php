<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\AuthException;
use Illuminate\Support\Facades\Log;

class FirebaseUserService
{
    protected $auth;
    protected $database;

    public function __construct()
    {
        $credentialsPath = base_path('storage/app/firebase_credentials.json');
        if (!file_exists($credentialsPath)) {
            Log::error('FIREBASE CREDENTIALS FILE NOT FOUND at ' . $credentialsPath);
        } elseif (!is_readable($credentialsPath)) {
            Log::error('FIREBASE CREDENTIALS FILE NOT READABLE at ' . $credentialsPath);
        } else {
            Log::info('FIREBASE CREDENTIALS FILE FOUND AND READABLE at ' . $credentialsPath);
        }
        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(config('firebase.database_url'));
        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    /**
     * Create a user in Firebase Auth and store profile in Realtime Database.
     *
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $role
     * @param array $extra (optional extra fields)
     * @return UserRecord|null
     */
    public function createUser($email, $password, $name, $role, $extra = [])
    {
        try {
            Log::info('Attempting to create user in Firebase Auth', ['email' => $email]);
            $user = $this->auth->createUser([
                'email' => $email,
                'password' => $password,
                'displayName' => $name,
            ]);
            Log::info('Firebase Auth user created', ['uid' => $user->uid]);

            $profile = array_merge([
                'uid' => $user->uid,
                'email' => $email,
                'name' => $name,
                'role' => $role,
            ], $extra);

            $this->database->getReference('users/' . $user->uid)->set($profile);
            Log::info('User profile written to Firebase Realtime Database', ['uid' => $user->uid]);

            return $user;
        } catch (EmailExists $e) {
            Log::error('Firebase Auth: Email already exists', ['email' => $email, 'error' => $e->getMessage()]);
            return null;
        } catch (AuthException $e) {
            Log::error('Firebase Auth error', ['email' => $email, 'error' => $e->getMessage()]);
            return null;
        } catch (\Exception $e) {
            Log::error('General Firebase sync error', ['email' => $email, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
