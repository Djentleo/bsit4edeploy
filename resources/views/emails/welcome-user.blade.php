
<body style="background: #f4f6fb; padding: 40px 0; font-family: 'Segoe UI', Arial, sans-serif;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 28px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="color: #1a237e; font-size: 1.8rem; margin: 0;">Welcome, {{ $user->name }}!</h1>
        </div>
        <p style="color: #333; font-size: 1.1rem;">Your account has been created for the <strong>Barangay Baritan Incident Report and Management System</strong>.</p>

        <div style="background: #f0f4ff; border-radius: 8px; padding: 18px 20px; margin: 24px 0;">
            <h3 style="color: #1a237e; margin-top: 0;">Account Credentials</h3>
            <ul style="list-style: none; padding: 0; margin: 0; color: #222;">
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
                <li><strong>Password:</strong> {{ $plainPassword }}</li>
            </ul>
        </div>

        <p style="color: #444;">You can now log in using these credentials. <span style="color: #d32f2f; font-weight: bold;">For security, please change your password after your first login.</span></p>
        <p style="color: #888; font-size: 0.95rem; margin-top: 32px; text-align: center;">&copy; {{ date('Y') }} Barangay Baritan, Malabon City</p>
    </div>
</body>
