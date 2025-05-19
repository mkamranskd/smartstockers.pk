function updateOrderStatus(orderId, newStatus) {
    const questions = {
        1: "Are you sure you want to mark this order as confirmed?",
        2: "Are you sure you want to mark this order as ready to ship?",
        3: "Are you sure you want to mark this order as shipped?",
        4: "Are you sure you want to mark this order as delivered?",
        5: "Are you sure you want to mark this order as pending?",
        6: "Are you sure you want to cancel this order?"
    };
    const answers = {
        1: "Confirmed Successfully!",
        2: "Ready to Ship Successfully!",
        3: "Shipped Successfully!",
        4: "Delivered Successfully!",
        5: "Pending Successfully!",
        6: "Cancelled Successfully!"
    }

    const answer = answers[newStatus] || "Updated Successfully?";
    const question = questions[newStatus] || "Are you sure you want to update this order?";

    if (confirm(question)) {

        showMessage('Please Wait!', 'danger');
        fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `order_id=${orderId}&new_status=${newStatus}`
            })
            .then(response => response.text())
            .then(data => {
                console.log("Server Response:", data); // Debugging
                if (data.trim() === "success") {
                    showMessage(answer, 'info');
                    loadOrders(newStatus);
                } else {
                    alert("Error: " + data);
                }
            })
            .catch(error => {
                console.error("Error updating order status:", error);
                showMessage("An error occurred while updating the status.", "danger");
            });

    }
}





function PostExConfirmOrderModal(orderId) {
    let row = document.getElementById("order-" + orderId);
    if (!row) {
        alert('Order details not found!');
        return;
    }

    let clientId = row.children[3]?.innerText.trim() || "";
    let customerPhone = row.children[7]?.innerText.trim() || "";
    let customerName = row.children[6]?.innerText.trim() || "N/A";
    let address = row.children[8]?.innerText.trim() || "N/A";
    let city = row.children[9]?.innerText.trim() || "N/A";
    let productDetails = row.children[10]?.innerText.trim() || "N/A";
    let cod = row.children[11]?.innerText.trim() || "N/A";
    let quantity = row.children[12]?.innerText.trim() || "0";
    let totalPrice = row.children[13]?.innerText.trim() || "0";
    let totalWeight = row.children[14]?.innerText.trim() || "0 KG";
    let status = row.children[15]?.innerText.trim() || "N/A";
    let deliveryService = row.children[1]?.innerText.trim() || "N/A";;

    document.getElementById("postex-order-content").innerHTML = `
                <table class="table table-bordered">
                    <tr><th>Order ID</th><td>${orderId}</td><th>Client Id</th><td id="client-id">${clientId}</td></tr>
                    <tr><th>Customer Name</th><td colspan="3">${customerName}</td></tr>
                    <tr><th>Phone</th><td colspan="3">${customerPhone}</td></tr>
                    <tr><th>City</th><td colspan="3">${city}</td></tr>
                    <tr><th>Address</th><td colspan="3">${address}</td></tr>
                    <tr><th>Products</th><td colspan="3">${productDetails}</td></tr>
                    <tr><th>COD</th><td>${cod}</td><th>Quantity</th><td>${quantity}</td></tr>
                    <tr><th>Total Weight</th><td>${totalWeight}</td><th>Status</th><td>${status}</td></tr>
                </table>
            `;

    document.querySelector('.btn-success').setAttribute("onclick",
        `shareToWhatsApp('${customerPhone}', '${customerName}', '${productDetails}', '${quantity}', '${cod}', '${address}', '${city}', '${deliveryService}')`
    );

    // Open Bootstrap modal
    var myModal = new bootstrap.Modal(document.getElementById('postex-order-modal'));
    myModal.show();
}

async function postExConfirmAndCreateOrder() {
    try {

        let orderId = document.querySelector("#postex-order-content table td")?.innerText.trim();
        let clientId = document.querySelector("#client-id")?.innerText.trim();

        if (!clientId || !orderId) {
            alert("Error: Missing Client ID or Order ID");
            return;
        }

        console.log("Client ID:", clientId);
        console.log("Order ID:", orderId);

        let apiToken = await fetchPostexApiToken(clientId);
        if (!apiToken) return;

        let orderData = {
            clientId,
            cityName: document.querySelector("#postex-order-content tr:nth-child(4) td")?.innerText.trim(),
            customerName: document.querySelector("#postex-order-content tr:nth-child(2) td")?.innerText.trim(),
            customerPhone: document.querySelector("#postex-order-content tr:nth-child(3) td")?.innerText.trim(),
            deliveryAddress: document.querySelector("#postex-order-content tr:nth-child(5) td")?.innerText
                .split(", ")[
                    0] || "N/A",
            invoicePayment: document.querySelector("#postex-order-content tr:nth-child(7) td")?.innerText
                .trim(),
            orderDetail: document.querySelector("#postex-order-content tr:nth-child(6) td")?.innerText.trim(),
            orderRefNumber: orderId,
            orderType: "Normal",
            pickupAddressCode: "001"
        };

        console.log("Order Data:", orderData);

        // ✅ Send order to PostEx API
        let orderResult = await createPostExOrder(orderData, apiToken);
        if (!orderResult) return;

       // ✅ If order is successfully created, save it to database
       if (orderResult.statusCode === "200" && orderResult.statusMessage === "ORDER HAS BEEN CREATED") {

    loadOrders(2);

    let saveOrderData = {
        
        orderId: orderId,
        clientId: clientId,
       trackingNumber: `PostEx-${orderResult.dist.trackingNumber}`,

        orderStatus: '2', // Status updated to 2
        updatedAt: new Date().toISOString()
    };

    console.log("🚀 Ready to save order to database:", saveOrderData);

    await saveOrderToDatabase(saveOrderData);
    showMessage("Order Created successfully on Postex", 'primary');
    bootstrap.Modal.getInstance(document.getElementById('postex-order-modal')).hide();
} else {
    alert("Order creation failed: " + orderResult.statusMessage);
}

    } catch (error) {
        console.error("Unexpected Error:", error);
        alert("Unexpected error: " + error.message);
    }
}

async function fetchPostexApiToken(clientId) {
    try {
        let response = await fetch(`get-postex-api-token.php?clientId=${encodeURIComponent(clientId)}`);

        if (!response.ok) throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);

        let data = await response.json();
        if (data.api_token) return data.api_token;
        throw new Error("API Token not found for clientId: " + clientId);
    } catch (error) {
        console.error("Error fetching API token:", error);
        showMessage(error.message, 'danger');
        return null;
    }
}

async function createPostExOrder(orderData, apiToken) {
    try {
        let response = await fetch("https://api.postex.pk/services/integration/api/order/v3/create-order", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "token": apiToken
            },
            body: JSON.stringify(orderData)
        });

        let result = await response.json();

        console.log("PostEx API Response:", result);

        if (result.statusCode !== "200") {
            const errorMessage = result.statusMessage || result.message || result.error || "Unknown error occurred.";

            if (errorMessage.toLowerCase().includes("invalid delivery city")) {
                alert("The entered city is not valid in PostEx's delivery list.");
            } else {
                alert(errorMessage);
            }

            return null;
        }

        showMessage('Order Created on Postex', 'primary');

        return {
            ok: true,
            statusCode: result.statusCode,
            statusMessage: result.statusMessage,
            dist: result.dist || {}
        };
    } catch (error) {
        console.error("Error creating PostEx order:", error);
        alert("Unexpected error: " + error.message);
        return null;
    }
}










function LeopardsConfirmOrderModal(orderId) {
    let row = document.getElementById("order-" + orderId);
    if (!row) {
        alert('Order details not found!');
        return;
    }

    let clientId = row.children[3]?.innerText.trim() || "";
    let customerPhone = row.children[7]?.innerText.trim() || "";
    let customerName = row.children[6]?.innerText.trim() || "N/A";
    let address = row.children[8]?.innerText.trim() || "N/A";
    let city = row.children[9]?.innerText.trim() || "N/A";
    let productDetails = row.children[10]?.innerText.trim() || "N/A";
    let cod = row.children[11]?.innerText.trim() || "N/A";
    let quantity = row.children[12]?.innerText.trim() || "0";
    let totalPrice = row.children[13]?.innerText.trim() || "0";
    let totalWeight = row.children[14]?.innerText.trim() || "0 KG";
    let status = row.children[15]?.innerText.trim() || "N/A";
    let deliveryService = row.children[1]?.innerText.trim() || "N/A";;

    document.getElementById("leopards-order-content").innerHTML = `
                <table class="table table-bordered">
                    <tr><th>Order ID</th><td>${orderId}</td><th>Client Id</th><td id="client-id">${clientId}</td></tr>
                    <tr><th>Customer Name</th><td colspan="3">${customerName}</td></tr>
                    <tr><th>Phone</th><td colspan="3">${customerPhone}</td></tr>
                    <tr><th>Address</th><td colspan="3">${address}</td></tr>
                    <tr><th>City</th><td id="UpdatedCityName">${city}</td>  
                         
                   
                        <td id="changeCity" colspan="4" style="display:none;">
                        <select class="form-select w-auto" id="leopardsCitiesDropdown" style="min-width:150px;"></select>

                        <button class="btn btn-success" onclick="updateSelectedCity();"><i class="fa fa-save"></i></button>
                        </td>
                        
                    </tr>
                    <tr><th>Products</th><td colspan="3">${productDetails}</td></tr>
                    <tr><th>COD</th><td>${cod}</td><th>Quantity</th><td>${quantity}</td></tr>
                    <tr><th>Total Weight</th><td>${totalWeight}</td><th>Status</th><td>${status}</td></tr>
                    <tr><th>Shipment Type</th>
                    
                   <td colspan="3">
                        <select class="form-select w-auto" id="shipmentType" style="min-width:150px;">
                            <option>OVERNIGHT</option>
                            <option>Economy</option>
                        </select>
                    </td>


                       

                        
                    </td>
                    
                    </tr>
                   
                </table>
            `;

    document.querySelector('.btn-success').setAttribute("onclick",
        `shareToWhatsApp('${customerPhone}', '${customerName}', '${productDetails}', '${quantity}', '${cod}', '${address}', '${city}', '${deliveryService}')`
    );

    // Open Bootstrap modal
    var myModal = new bootstrap.Modal(document.getElementById('leopards-order-modal'));
    myModal.show();
}
async function leopardsConfirmAndCreateOrder() {
    try {
        // ✅ Extract order details
        let orderId = document.querySelector("#leopards-order-content table td")?.innerText.trim();
        let clientId = document.querySelector("#client-id")?.innerText.trim();

        if (!clientId || !orderId) {
            alert("Error: Missing Client ID or Order ID");
            return;
        }

        console.log("Client ID:", clientId);
        console.log("Order ID:", orderId);

        // ✅ Fetch API Token for Leopards
        let apiToken = await fetchLeopardsApiToken(clientId);
        if (!apiToken) return;

        console.log("Fetched API Token:", apiToken); // ✅ log here

        if (!apiToken) return;

        // ✅ Prepare order data for Leopards API
        let orderData = {

            api_key: apiToken.api_key, // ✅ Make sure this is NOT undefined or empty
            api_password: '123456',
            booked_packet_weight: parseInt(document.querySelector("#leopards-order-content tr:nth-child(7) td")
                ?.innerText.trim()) || 500,
            booked_packet_no_piece:parseInt(document.querySelector("#leopards-order-content tr:nth-child(7) td:nth-child(4)")
                ?.innerText.trim()) || 500,             // Number of pieces
            booked_packet_collect_amount: parseInt(document.querySelector(
                    "#leopards-order-content tr:nth-child(7) td")
                ?.innerText.trim()) || 500, // Collection amount on delivery
            booked_packet_order_id: orderId, // Optional Order ID (can be the order ID from your system)
            origin_city: 'self', // Use 'self' or provide a city ID
            destination_city: document.querySelector("#leopards-order-content tr:nth-child(5) td")?.innerText
                .trim(),
            shipment_name_eng: "self",
            shipment_address: "self",
            shipment_phone: "self",
            shipment_address: "self",
            consignment_name_eng: document.querySelector("#leopards-order-content tr:nth-child(2) td")
                ?.innerText.trim(),
            consignment_email: '', // Sample email for consignment
            consignment_phone: document.querySelector("#leopards-order-content tr:nth-child(3) td")?.innerText
                .trim(),
            consignment_address: document.querySelector("#leopards-order-content tr:nth-child(4) td")?.innerText
                .trim(),
            special_instructions:  document.querySelector("#leopards-order-content tr:nth-child(6) td")?.innerText
            .trim(),
            shipment_type: document.querySelector('#shipmentType')?.selectedOptions[0]?.text.toLowerCase()
        };

        console.log("Leopards Order Data:", orderData);

        // ✅ Send order to Leopards API
        let orderResult = await sendOrderViaBackend(orderData);


        if (!orderResult) return;

        // ✅ If order is successfully created, save it to database
        if (orderResult.status === 1) {
            loadOrders(2);
            let saveOrderData = {
                orderId: orderId,
                clientId: clientId,
                trackingNumber: `Leopards-${orderResult.track_number}`,
                orderStatus: '2', // Status updated to 2
                updatedAt: new Date().toISOString()
            };

            console.log("Saving Order:", saveOrderData);

            // Call saveOrderToDatabase after successful order creation
            await saveOrderToDatabase(saveOrderData);
            showMessage("Order Created successfully on Leopards ", 'primary');
            bootstrap.Modal.getInstance(document.getElementById('leopards-order-modal')).hide();
        } else {
            // alert("Order creation failed: " + orderResult.error);
            if (orderResult.error.toLowerCase().includes("city")) {
                alert("The entered city is not valid in Leopards' city list.");
                document.getElementById("changeCity").style.display = 'block';
                fetchLeopardsCities("leopardsCitiesDropdown");

            } else if (orderResult.error.toLowerCase().includes("Connection ")){
                alert("Internet Problem Occurs, Please Try Again Later");
            } else{
                alert(errorMessage);
            }
        }
    } catch (error) {
        console.error("Unexpected Error:", error);
        alert("Unexpected error: " + error.message);
    }
}
async function fetchLeopardsApiToken(clientId) {
    try {
        let response = await fetch(`get-leopards-api-token.php?clientId=${encodeURIComponent(clientId)}`);
        if (!response.ok) throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);

        let data = await response.json();
        console.log("Fetched API Token:", data); // Debug: Check the API token returned from PHP
        if (data.api_token) return {
            api_key: data.api_token.trim()
        };
        if (data.api_token) {
            return {
                api_key: data.api_token
            };
        } else {
            throw new Error("API Token not found for clientId: " + clientId);
        }
    } catch (error) {
        console.error("Error fetching Leopards API token:", error);
        alert("Error fetching API token: " + error.message);
        return null;
    }
}
async function createLeopardsOrder(orderData) {
    try {
        let response = await fetch('https://merchantapi.leopardscourier.com/api/bookPacket/format/json/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData),
        });

        let result = await response.json();

        // 🔍 Show full raw response for debugging (optional)
        console.log("Leopards Order Response:", result);

        // ❌ If response is not OK or status indicates error
        if (!response.ok || result?.status === 0) {
            const errorMessage = result?.message || result?.remarks || "Unknown error occurred.";;
            return null;
        }       
        return result;

    } catch (error) {
        console.error("Error creating Leopards order:", error);
        alert("Unexpected error: " + error.message);
        return null;
    }
}
async function sendOrderViaBackend(orderData) {


    try {
        console.log("Final Order Data to Backend:", JSON.stringify(orderData, null, 2));

        const response = await fetch('book-leopards-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        // Read the response as text first
        const text = await response.text();
       


        // Try parsing it as JSON
        try {
            const result = JSON.parse(text);
            console.log("✅ Backend Response (JSON):", result);
            
            return result;
        } catch (jsonError) {
            console.error("❌ Backend Response is NOT JSON. Raw response:", text);
            alert(
                "Error sending order: Response is not valid JSON. Check the console or server logs for more details."
            );
            return null;
        }
    } catch (error) {
        console.error("🚨 Network or Fetch Error:", error);
        alert("Error sending order: " + error.message);
        return null;
    }
}
function updateSelectedCity() {
    var dropdown = document.getElementById("leopardsCitiesDropdown");
    var selectedText = dropdown.options[dropdown.selectedIndex].value; // Get selected option text
    document.getElementById("UpdatedCityName").innerHTML = selectedText;
    document.getElementById("leopardsCitiesDropdown").addEventListener("change", function() {
        orderData.city = this.value; // Update the orderData object
    });
}
document.getElementById("leopardsCitiesDropdown").addEventListener("change", updateSelectedCity);
async function fetchLeopardsCities(selectElementId) {
    try {
        let apiCredentials = {
            api_key: "487F7B22F68312D2C1BBC93B1AEA445B1742923857", // Replace with actual API key
            api_password: "123456" // Replace with actual API password
        };

        let response = await fetch("https://merchantapi.leopardscourier.com/api/getAllCities/format/json/", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(apiCredentials)
        });

        let data = await response.json();
        console.log("Leopards API Response:", data); // Log response for debugging

        if (data.status !== 1 || !Array.isArray(data.city_list)) {
            console.error("Error fetching cities:", data.error || "Invalid response format");
            return;
        }

        // Sort cities alphabetically by name
        data.city_list.sort((a, b) => a.name.localeCompare(b.name));

        let selectElement = document.getElementById(selectElementId);
        if (!selectElement) {
            console.error("Select element not found with ID:", selectElementId);
            return;
        }

        // Populate select dropdown with city names
        selectElement.innerHTML = `<option value="">Select a City</option>` +
            data.city_list.map(city => `<option value="${city.id}">${city.name}</option>`).join("");

    } catch (error) {
        console.error("Error fetching cities:", error);
    }
}
// 🔹 Function to save order in local database
async function saveOrderToDatabase(orderData) {
    console.log("📦 saveOrderToDatabase() called with:", orderData); // Add this
    try {
        let response = await fetch("save-order.php", { // ✅ Fixed backslashes
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(orderData),
        });
        let text = await response.text();
        console.log("Save Order Response:", text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (error) {
            console.error("Invalid JSON response from server:", text);
            throw new Error("Invalid JSON response from server");
        }
        if (!data.success) {
            throw new Error(data.error || "Unknown error");
        }
    } catch (error) {
        console.error("Error saving order:", error);
        showMessage("Error saving order: " + error.message, 'danger');
    }
}

function sortTable(columnIndex, element) {
    let table = document.getElementById("myTable");
    let tbody = table.getElementsByTagName("tbody")[0];
    let rows = Array.from(tbody.getElementsByTagName("tr"));

    let isAscending = element.getAttribute("data-sort") === "asc";

    rows.sort((rowA, rowB) => {
        let cellA = rowA.getElementsByTagName("td")[columnIndex].innerText.trim();
        let cellB = rowB.getElementsByTagName("td")[columnIndex].innerText.trim();

        // Handle numbers
        if (!isNaN(cellA) && !isNaN(cellB)) {
            return isAscending ? cellA - cellB : cellB - cellA;
        }

        // Handle dates
        let dateA = Date.parse(cellA);
        let dateB = Date.parse(cellB);
        if (!isNaN(dateA) && !isNaN(dateB)) {
            return isAscending ? dateA - dateB : dateB - dateA;
        }

        // Handle text (case-insensitive sorting)
        return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
    });

    // Reorder the rows in the table
    rows.forEach(row => tbody.appendChild(row));

    // Reset all icons
    document.querySelectorAll("th i").forEach(icon => {
        icon.className = "fa fa-sort";
    });

    // Toggle sort order & update icon
    element.setAttribute("data-sort", isAscending ? "desc" : "asc");
    let icon = element.querySelector("i");
    icon.className = isAscending ? "fa fa-sort-down" : "fa fa-sort-up";
}

function resetActiveTabs(activeTab) {
    // Remove the 'active' class from all tabs
    document.querySelectorAll("#order-tabs .tab-nav").forEach(tab => {
        tab.classList.remove("active");
    });

    // Force reflow to ensure the "active" class is applied properly
    void activeTab.offsetWidth;

    // Add the 'active' class to the clicked tab
    activeTab.classList.add("active");

    // Store the active tab's status in localStorage to persist selection
    localStorage.setItem("activeOrderTab", activeTab.getAttribute("data-status"));
}






function generateAWB(orderId) {
    let row = document.getElementById("order-" + orderId);
    if (!row) {
        showMessage('Order details not found!', 'danger');
        return;
    }

    let customerPhone = row.children[7]?.innerText.trim() || "";
    let customerName = row.children[6]?.innerText.trim() || "N/A";
    let address = row.children[8]?.innerText.trim() || "N/A";
    let city = row.children[9]?.innerText.trim() || "N/A";
    let productDetails = row.children[10]?.innerText.trim() || "N/A"; // Using innerHTML to preserve line breaks
    let cod = row.children[11]?.innerText.trim() || "N/A";
    let quantity = row.children[12]?.innerText.trim() || "0";
    let totalPrice = row.children[13]?.innerText.trim() || "0";
    let totalWeight = row.children[14]?.innerText.trim() || "0 KG";
    let status = row.children[15]?.innerText.trim() || "N/A";
    let deliveryService = row.children[16]?.innerText.trim() || "N/A";
    let trackingId = row.children[17]?.innerText.trim() || "Not Shipped Yet";


    document.getElementById("awb-content").innerHTML = `
        <table class="table table-bordered" style="border: 2px solid black; width: 100%; text-align: left;">
        <tbody>

            <tr>

            <td rowspan="2" style="text-align: center;">
                <img src="../img/logo.png" alt="Logo" style="max-width: 80px;">
            </td>
                <th style="width:12%; height:20px;">Order ID</th>
                <td style="width:22%; height:20px;" >${orderId}</td>

                <th style="width:13%;">Delivery Service </th>
                <td style="width:10%;">${deliveryService}</td>
                <td rowspan="2"style="width:10%; text-align:center;">
                <span style="font-family: 'Libre Barcode 39';font-size: 30px;">${trackingId}</span>  <br>  <br>  
                ${trackingId}

                </td>

            </tr>

            <tr>
                <th>Status</th>
                <td>${status}</td>
                <th>Tracking ID</th>
                <td>${trackingId}</td>
            </tr>
        </tbody>
    </table>
    <table class="table table-bordered" style="border: 2px solid black; width: 100%; text-align: left;">
        <tbody>
            <tr>
                <td colspan="14">
                    <strong>Customer Details</strong>
                </td>
                <td colspan="5">
                    <strong>Product Details</strong>
                </td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td colspan="13">${customerName}</td>

                <td colspan="4">${productDetails}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="13">${address}, ${city}</td>
                <th colspan="2">Quantity</th>
                <td colspan="2">${quantity}</td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td colspan="13">${customerPhone}</td>
                <th>Total Weight</th>
                <td>${totalWeight}</td>
                <th>COD</th>
                <td>${cod}</td>
            </tr>
        </tbody>
    </table>

    `;

    // Update the Share to WhatsApp button to include order data
    document.querySelector('.btn-success').setAttribute("onclick",
        `shareToWhatsApp('${customerPhone}', '${customerName}', '${productDetails}', '${quantity}', '${totalPrice}', '${address}', '${city}', '${deliveryService}')`
    );

    // Open the Bootstrap modal
    $('#awb-modal').modal('show');
}

function closeAWBModal() {
    document.getElementById("awb-modal").style.display = "none";
}

function filterByCustomDate() {

    let startDate = document.getElementById("startDate").value;
    let endDate = document.getElementById("endDate").value;

    if (!startDate || !endDate) {
        showMessage("Please select both start and end dates.", 'danger');
        return;
    }

    fetch(`fetch_orders.php?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById("orders-content").innerHTML = data;
        })
        .catch(error => console.error("Error fetching filtered orders:", error));
}

function downloadPDF() {
    const {
        jsPDF
    } = window.jspdf;
    const content = document.getElementById("awb-content");

    // Create a new jsPDF instance
    const pdf = new jsPDF("l", "mm", "a5");

    // Use jsPDF's html method to render the HTML directly
    pdf.html(content, {
        callback: function(doc) {
            // Save the generated PDF
            doc.save("AWB.pdf");
        },
        margin: [10, 10, 10, 10], // Top, Left, Bottom, Right margins
        x: 10, // Horizontal position on the page
        y: 10, // Vertical position on the page
        width: 180, // Width of the page content
        windowWidth: 800, // Window width for accurate scaling
    });
}

function printAWB() {
    let printContent = document.getElementById("awb-content").innerHTML;
    let win = window.open('', '', 'width=800,height=600');

    win.document.write(`
        <html>
        <head>
            <title>Print AWB</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 2px solid black;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);

    win.document.close();
    win.print();
}



