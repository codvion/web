<?php
$seo_description = 'User Profile Dashboard - Manage your account details securely.';
$seo_keywords = 'user profile, dashboard, account settings';
$seo_canonical = 'https://logicloop.wuaze.com/profile';
require 'config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pagetitle = 'Profile - Logic Loop';
$header_nav['profile'] = 'active';
if (!isset($_SESSION['user_user'])) {
    header("Location: login");
    exit();
}
$username = $_SESSION['user_user'];
$stmt = $conn->prepare("SELECT * FROM login_system WHERE user_name=?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    if (!empty($_FILES['profile_pic']['name'])) {
        $img_name = $_FILES['profile_pic']['name'];
        $tmp_name = $_FILES['profile_pic']['tmp_name'];
        $size     = $_FILES['profile_pic']['size'];
        $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $response['message'] = "Only JPG, JPEG, PNG, and WEBP files are allowed!";
            echo json_encode($response);
            exit();
        }
        if ($size > 2 * 1024 * 1024) {
            $response['message'] = "File size must be less than 2MB!";
            echo json_encode($response);
            exit();
        }
        $new_name = $username . '.' . $ext;
        $folder   = "uploads/" . $new_name;
        if (file_exists($folder)) {
            unlink($folder);
        }
        if (move_uploaded_file($tmp_name, $folder)) {
            $stmt = $conn->prepare("UPDATE login_system SET profile_pic=? WHERE user_name=?");
            $stmt->execute([$new_name, $username]);
            $response['success'] = true;
            $response['message'] = "Profile picture updated successfully!";
            $response['image_url'] = $folder;
        } else {
            $response['message'] = "Upload failed! Please try again.";
        }
    } else {
        $response['message'] = "No file selected.";
    }
    echo json_encode($response);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = htmlspecialchars(trim($_POST['fullname']));
    $bio       = htmlspecialchars(trim($_POST['bio']));
    $address   = htmlspecialchars(trim($_POST['address']));
    $stmt = $conn->prepare("UPDATE login_system SET full_name=?, bio=?, address=? WHERE user_name=?");
    $stmt->execute([$full_name, $bio, $address, $username]);
    $success = "Profile Updated Successfully!";
    $stmt = $conn->prepare("SELECT * FROM login_system WHERE user_name=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
include 'components/header.php';
?>

<style>
.profile-container {
    width: 100%;
    max-width: 1200px;
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 0px auto;
}

.profile-sidebar {
    width: 260px;
    background: #020508;
    padding: 25px;
    border-right: 1px solid rgba(255,255,255,0.05);
}

.profile-sidebar h2 {
    color: #00f5ff;
    margin-bottom: 30px;
}

.profile-sidebar ul {
    list-style: none;
    padding: 0;
}

.profile-sidebar ul li {
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}

.profile-sidebar ul li:hover {
    background: #0a1520;
}

.profile-sidebar ul li.active {
    background: #00f5ff;
    color: #000;
}

.profile-sidebar ul li a {
    display: block;
    text-decoration: none;
    color: inherit;
}

.profile-content {
    flex: 1;
    padding: 40px;
}

.profile-content h2 {
    margin-bottom: 20px;
}

.profile-box {
    width: 100%;
    background: #0a1520;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,245,255,0.1);
}

form label {
    display: block;
    margin: 10px 0 5px;
    font-size: 14px;
    color: #aaa;
}

form input,
form textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #1c2b36;
    background: #050a0f;
    color: #fff;
    outline: none;
    transition: 0.3s;
}

form input:focus,
form textarea:focus {
    border-color: #00f5ff;
    box-shadow: 0 0 10px rgba(0,245,255,0.3);
}

textarea {
    resize: none;
    height: 100px;
}

input[disabled] {
    background: #111;
    color: #777;
    cursor: not-allowed;
}

.btn-save {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(45deg, #00f5ff);
    color: #000;
    font-weight: bold;
    transition: 0.3s;
}

.btn-save:hover {
    transform: scale(1.03);
    box-shadow: 0 0 15px rgba(0,245,255,0.5);
}

@media(max-width: 768px) {
    .profile-container {
        flex-direction: column;
    }

    .profile-sidebar {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .profile-sidebar ul {
        display: flex;
        gap: 10px;
    }

    .profile-content {
        padding: 20px;
    }

}
.profile-image-wrapper {
    display: flex;
    justify-content: center;
    flex-direction: column;
    gap: 5px;
    align-items: center;
    margin-bottom: 15px;
}

.profile-image-box {
    position: relative;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 3px solid #00f5ff;
}

.profile-image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.profile-image-box .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    color: #00f5ff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 14px;
    opacity: 0;
    transition: 0.3s;
}

.profile-image-box:hover .overlay {
    opacity: 1;
}

.profile-image-box:hover {
    box-shadow: 0 0 15px rgba(0,245,255,0.7);
}
.image-message-system{
    font-size: 0.72rem;
    font-weight: 500;
    min-height: 16px;
    padding-left: 2px;
    display: flex;
    align-items: center;
    text-align: center;
    gap: 4px;
    opacity: 1;
}

.image-message-system.success {
    color: #22c55e !important;
}

.image-message-system.error {
    color: #f87171 !important;
}

.profile-message-system {
    position: fixed;
    top: 95px;
    left: 50%;
    transform: translateX(-50%);
    padding: 14px 18px;
    border-radius: 10px;
    font-size: clamp(11px, 0.85vw, 14px);
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(145deg, var(--panel), var(--darker));
    color: var(--text);
    border: 1px solid var(--electric);
    box-shadow: var(--glow);
    overflow: hidden;
    z-index: 1;
    letter-spacing: 0.5px;
}

.profile-message-system::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent, rgba(0, 245, 255, 0.2), transparent);
    opacity: 0;
    transition: 0.4s;
}

.profile-message-system:hover::before {
    opacity: 1;
}

.profile-message-system.success {
    border-color: var(--electric);
    box-shadow: 0 0 15px rgba(0, 245, 255, 0.6);
}

@keyframes pulseGlow {
    0% {
        box-shadow: 0 0 10px rgba(0, 245, 255, .3);
    }

    50% {
        box-shadow: 0 0 25px rgba(0, 245, 255, .6);
    }

    100% {
        box-shadow: 0 0 10px rgba(0, 245, 255, .3);
    }
}

.profile-message-system.success {
    animation: pulseGlow 2s infinite;
}

</style>
<?php if(isset($success)){ ?>
    <div class="profile-message-system"><?php echo htmlspecialchars($success);
    unset($success); ?></div>
<?php } ?>
<div class="profile-container">
    <div class="profile-sidebar">
        <ul>
            <div class="profile-image-wrapper">
                <label for="profileUpload" class="profile-image-box">
                <img id="profilePreview"
                    src="<?php echo !empty($user['profile_pic']) ? 'uploads/'.$user['profile_pic'] : 'images/default.png'; ?>"
                    alt="Profile">
                <div class="overlay">
                    <span>Change Photo</span>
                </div>
                </label>
                <input type="file" name="profile_pic" id="profileUpload" hidden>
               <div class="image-message-system" id="imageUploadMessage"></div>
            </div>
        </ul>
        <ul>
            <li class="active">Profile</li>
            <li><a href="index">Home</a></li>
            <li><a href="logout">Logout</a></li>
        </ul>
    </div>

    <div class="profile-content">
        <h2>My Profile</h2>
        <div class="profile-box">
            <form method="POST" enctype="multipart/form-data">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                <label>Username</label>
                <input type="text" value="<?php echo $user['user_name']; ?>" disabled>
                <label>Email</label>
                <input type="email" value="<?php echo $user['email']; ?>" disabled>
                <label>Phone</label>
                <input type="text" value="<?php echo $user['phone']; ?>" disabled>
                <label>Bio</label>
                <textarea name="bio"><?php echo $user['bio'] ?? ''; ?></textarea>
                <label>Address</label>
                <input type="text" name="address" value="<?php echo $user['address'] ?? ''; ?>">
                <button type="submit" class="btn-save">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById("profileUpload").addEventListener("change", function(e) {
    const file = e.target.files[0];
    const msgDiv = document.getElementById("imageUploadMessage");
    const previewImg = document.getElementById("profilePreview");
    if (!file) return;
    const formData = new FormData();
    formData.append("profile_pic", file);
    formData.append("ajax", "1");

    fetch("profile", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        msgDiv.classList.remove("success", "error");
        if (data.success) {
            msgDiv.classList.add("success");
            msgDiv.textContent = data.message;
            if (data.image_url) {
                const cacheBuster = data.filetime ? data.filetime : new Date().getTime();
                previewImg.src = data.image_url + "?v=" + cacheBuster;
                console.log("Image updated to:", previewImg.src);
            } else {
                console.warn("No image_url in response");
            }
        } else {
            msgDiv.classList.add("error");
            msgDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error("Upload error:", error);
        msgDiv.classList.add("error");
        msgDiv.textContent = "Network error or server issue. Please try again.";
    });
});
</script>

<script src="scripts/script.js"></script>
<?php include 'components/footerpre.php'; ?>
<?php include 'components/footer.php'; ?>