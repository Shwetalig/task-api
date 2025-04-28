# Set Base URL
$baseUrl = "http://localhost/task-api/public"

# Sample user data
$sampleUsername = "testuser"  # New field for username
$sampleEmail = "testuser@example.com"
$samplePassword = "Test@1234"

# Register
Write-Host " Testing: Register"
$response = Invoke-WebRequest -Uri "$baseUrl/api/register" -Method POST -Body @{
    username = $sampleUsername  # Include username in registration
    email = $sampleEmail
    password = $samplePassword
} -ContentType "application/x-www-form-urlencoded" -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# Login
Write-Host " Testing: Login"
$response = Invoke-WebRequest -Uri "$baseUrl/api/login" -Method POST -Body @{
    username = $sampleUsername  # Login using username
    email = $sampleEmail        # Ensure email is included
    password = $samplePassword
} -ContentType "application/x-www-form-urlencoded" -UseBasicParsing
$loginData = $response.Content | ConvertFrom-Json
$token = $loginData.token
Write-Host "Token:" $token
Write-Host "`n"

# Get Profile
Write-Host " Testing: Get Profile"
$response = Invoke-WebRequest -Uri "$baseUrl/api/profile" -Method GET -Headers @{
    Authorization = "Bearer $token"
} -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# Update Profile
Write-Host " Testing: Update Profile"
$response = Invoke-WebRequest -Uri "$baseUrl/api/profile" -Method PUT -Headers @{
    Authorization = "Bearer $token"
} -Body @{
    name = "Updated User"
} -ContentType "application/x-www-form-urlencoded" -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# Forgot Password
Write-Host " Testing: Forgot Password"
$response = Invoke-WebRequest -Uri "$baseUrl/api/forgot-password" -Method POST -Body @{
    email = $sampleEmail
} -ContentType "application/x-www-form-urlencoded" -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# Reset Password
Write-Host " Testing: Reset Password"
$response = Invoke-WebRequest -Uri "$baseUrl/api/reset-password" -Method POST -Body @{
    email = $sampleEmail
    token = "sample-reset-token"   # Replace this with the real reset token if needed
    password = "NewTest@1234"
} -ContentType "application/x-www-form-urlencoded" -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# Soft Delete a Task
$taskId = "123"  # Replace with the actual task ID you want to delete
Write-Host " Testing: Soft Delete Task"
$response = Invoke-WebRequest -Uri "$baseUrl/api/tasks/$taskId" -Method DELETE -Headers @{
    Authorization = "Bearer $token"
} -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# List Trashed Tasks
Write-Host " Testing: List Trashed Tasks"
$response = Invoke-WebRequest -Uri "$baseUrl/api/tasks/trashed" -Method GET -Headers @{
    Authorization = "Bearer $token"
} -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

# Restore a Trashed Task
$trashedTaskId = "123"  # Replace with the actual trashed task ID you want to restore
Write-Host " Testing: Restore Trashed Task"
$response = Invoke-WebRequest -Uri "$baseUrl/api/tasks/restore/$trashedTaskId" -Method PUT -Headers @{
    Authorization = "Bearer $token"
} -UseBasicParsing
$response.Content | ConvertFrom-Json | Format-List
Write-Host "`n"

Write-Host " All API tests completed."
