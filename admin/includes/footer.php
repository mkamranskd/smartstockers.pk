<div class="container my-4">
    <div class="">
        <br>
    </div>
</div>

<!-- Footer Start -->

<div class=" footer">
    <div class="bg-light rounded-top p-4">
        <div class="row">
            <div class="col-12 col-sm-6 text-center text-sm-start">
                &copy; <a href="index.php">Smart Stockers</a>, All Right Reserved.
            </div>
            <div class="col-12 col-sm-6 text-center text-sm-end">
                <small>Developed By <strong>Nexvel Hub</strong>.</small>
            </div>
        </div>
    </div>
</div>

<!-- Footer End -->

<!-- Content End -->

<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>

<!-- SmartAlert Test -->
<!--
 SmartAlert(" Please confirm...").then(()=> window.location.href='place_new_order.php'); 
 -->
<!-- SmartAlert Modal -->
<div id="smartAlertModal"
    style="display:none; position:fixed; top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;font-family:sans-serif;">
    <div id="smartAlertCard"
        style="background:#fff;border-radius:10px;width:90%;max-width:400px;box-shadow:0 4px 12px rgba(0,0,0,0.2);padding:20px;transform:scale(0.8);opacity:0;transition:all 0.25s ease;">
        <div id="smartAlertMessage" style="margin-bottom:15px;font-size:16px;color:#333;"></div>
        <div style="text-align:right;"><button id="smartAlertOk"
                style="padding:6px 14px;font-size:14px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;">OK</button>
        </div>
    </div>
</div>

<script>
const SmartAlert = msg => new Promise(res => {
    smartAlertMessage.innerText = msg;
    smartAlertModal.style.display = 'flex';
    requestAnimationFrame(() => {
        smartAlertCard.style.transform = 'scale(1)';
        smartAlertCard.style.opacity = '1';
    });
    const close = () => {
        smartAlertCard.style.transform = 'scale(0.8)';
        smartAlertCard.style.opacity = '0';
        setTimeout(() => smartAlertModal.style.display = 'none', 200);
        ok.removeEventListener('click', close);
        res();
    };
    const ok = document.getElementById('smartAlertOk');
    ok.addEventListener('click', close);
});
</script>

<!-- jQuery (only one version is needed) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Utility Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Bootstrap 5 JavaScript (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



<script src="../js/main.js"></script>
</body>

</html>