# -----------------------
# Forgot Password & Reset Password API Test Script
# -----------------------

# API base URL
$baseUrl = "http://localhost/task-api/public/api"

# Username to test
$username = "testuser"

# Step 1: Forgot Password - Request Reset Token
Write-Host "` Requesting reset token for username: $username..."
$forgotPasswordResponse = Invoke-WebRequest -Uri "$baseUrl/forgot-password" `
    -Method POST `
    -Headers @{
        "Content-Type" = "application/json"
    } `
    -Body (@{ username = $username } | ConvertTo-Json)

$forgotPasswordData = $forgotPasswordResponse.Content | ConvertFrom-Json
$resetToken = $forgotPasswordData.token

Write-Host " Reset token received: $resetToken"

# Step 2: Reset Password using the token
$newPassword = "newpassword123"

Write-Host "` Resetting password for username: $username with new password: $newPassword..."
$resetPasswordResponse = Invoke-WebRequest -Uri "$baseUrl/reset-password" `
    -Method POST `
    -Headers @{
        "Content-Type" = "application/json"
    } `
    -Body (@{
        username = $username
        token = $resetToken
        new_password = $newPassword
    } | ConvertTo-Json)

$resetPasswordData = $resetPasswordResponse.Content | ConvertFrom-Json

Write-Host " Password Reset Response:"
Write-Host ($resetPasswordData | ConvertTo-Json -Depth 5)
