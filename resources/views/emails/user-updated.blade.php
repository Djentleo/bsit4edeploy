<body style="background: #f4f6fb; padding: 40px 0; font-family: 'Segoe UI', Arial, sans-serif;">
    <div
        style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px 28px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="color: #1a237e; font-size: 1.6rem; margin: 0;">Your Account Information Was Updated</h1>
        </div>
        <p style="color: #333; font-size: 1.1rem;">Hello {{ $user->name }},</p>
        <p style="color: #333;">Your account details for <strong>Barangay Baritan Incident Report and Management
                System</strong> have been updated. Here is your latest information:</p>
        <div style="background: #f0f4ff; border-radius: 8px; padding: 18px 20px; margin: 24px 0;">
            <h3 style="color: #1a237e; margin-top: 0;">Updated Account Details</h3>
            <ul style="list-style: none; padding: 0; margin: 0; color: #222;">
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Username:</strong> {{ $user->username }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
                @if($user->responder_type)
                <li><strong>Responder Type:</strong> {{ ucfirst($user->responder_type) }}</li>
                @endif
                <li><strong>Mobile:</strong> {{ $user->mobile }}</li>
                <li><strong>Assigned Area:</strong> {{ $user->assigned_area }}</li>
            </ul>
        </div>
        <p style="color: #444;">If you did not request this change, please contact the system administrator immediately.
        </p>
        <p style="color: #888; font-size: 0.95rem; margin-top: 32px; text-align: center;">&copy; {{ date('Y') }}
            Barangay Baritan, Malabon City</p>
    </div>
</body>