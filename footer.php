<?php
/**
 * Hotel Reservation System - Footer Template
 * Public pages ONLY
 * PHP 7.4+
 */

// Detect admin pages
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
?>

<?php if (!$isAdminPage): ?>
    </main><!-- End Main Content -->

    <!-- ================= FOOTER ================= -->
    <footer class="footer" id="contact">
        <div class="footer-container">

            <!-- Footer Top -->
            <div class="footer-top">

                <!-- About -->
                <div class="footer-section footer-about" data-aos="fade-up">
                    <div class="footer-logo">
                        <i class="fas fa-hotel"></i>
                        <span>Abdulrasaq LuxuryHotel</span>
                    </div>
                    <p>
                        Experience the perfect blend of luxury, comfort, and exceptional service.
                        Your unforgettable stay awaits.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section footer-links" data-aos="fade-up" data-aos-delay="100">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                        <li><a href="<?= BASE_URL ?>rooms.php">Our Rooms</a></li>
                        <li><a href="<?= BASE_URL ?>reservation.php">Make Reservation</a></li>
                        <li><a href="<?= BASE_URL ?>contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-section footer-services" data-aos="fade-up" data-aos-delay="200">
                    <h3>Our Services</h3>
                    <ul>
                        <li>Room Booking</li>
                        <li>Event Hosting</li>
                        <li>Restaurant & Bar</li>
                        <li>Spa & Wellness</li>
                        <li>Conference Rooms</li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-section footer-contact" data-aos="fade-up" data-aos-delay="300">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Luxury Avenue, Downtown</p>
                    <p><i class="fas fa-phone"></i> +234 (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@AbdulrasaqLuxuryhotel.com</p>
                    <p><i class="fas fa-clock"></i> 24/7 Reception</p>
                </div>

            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>© <?= date('Y') ?> Abdulrasaq Luxury Hotel. All rights reserved.</p>
            </div>

        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
<?php endif; ?>

<!-- ================= SCRIPTS ================= -->

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
</script>

</body>
</html>
