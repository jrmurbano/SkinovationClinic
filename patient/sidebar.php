<aside class="sidebar">
    <div class="text-center mb-4">
        <img src="../assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png" alt="Skinovation Logo" class="img-fluid">
    </div>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="my_appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
        <li><a href="my_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
    </ul>
    <div class="logout-section">
        <a href="../logout.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<style>
.sidebar {
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    background-color: #f8f9fa;
    padding: 1rem;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.sidebar ul {
    list-style: none;
    padding: 0;
}
.sidebar ul li {
    margin-bottom: 1rem;
}
.sidebar ul li a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
}
.sidebar ul li a:hover {
    color: #6f42c1;
}
.logout-section {
    margin-top: auto;
    text-align: center;
}
.logout-button {
    text-decoration: none;
    color: #333;
    font-weight: 500;
}
.logout-button:hover {
    color: #6f42c1;
}
</style>
