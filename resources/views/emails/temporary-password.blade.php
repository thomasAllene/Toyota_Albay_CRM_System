<div>
    <h2>Hello {{ $user->first_name }},</h2>
    
    <p>Your temporary password has been generated. Please use this password to login:</p>
    
    <p><strong>{{ $password }}</strong></p>
    
    <p>For security reasons, we recommend changing your password after logging in.</p>
    
    <p>Best regards,<br>
    Your Application Team</p>
</div> 