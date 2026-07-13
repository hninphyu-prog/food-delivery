document.addEventListener('DOMContentLoaded', () => {

    // (1) Predefined Cancellation Reasons
    const CANCELLATION_REASONS = [
        "Restaurant is closed", 
        "Item out of stock",    
        "Preparation time too long", 
        "Customer requested cancellation", 
        "System error / Order rejected by restaurant", 
        "Other (Please specify)" 
    ];

    // (2) Function to display the Custom Combo Box Dialog
    function showCancellationDialog(orderId) {
        // Dialog (Modal) UI built with JavaScript
        const overlay = document.createElement('div');
        // Basic styling for the modal overlay
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;';
        
        const dialog = document.createElement('div');
        // Basic styling for the dialog box
        dialog.style.cssText = 'background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px;';
        
        // HTML content for the dialog box
        dialog.innerHTML = `
            <h3>Cancel Order #${orderId}</h3>
            <label for="cancel-reason-select" style="display: block; margin-bottom: 5px;">Select Reason:</label>
            <select id="cancel-reason-select" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="" disabled selected>-- Select a Reason --</option>
                ${CANCELLATION_REASONS.map(reason => `<option value="${reason}">${reason}</option>`).join('')}
            </select>
            <div id="other-reason-group" style="margin-bottom: 15px; display: none;">
                <label for="other-reason-input" style="display: block; margin-bottom: 5px;">Specify Other Reason:</label>
                <textarea id="other-reason-input" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button id="dialog-cancel-btn" style="padding: 8px 15px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button id="dialog-submit-btn" style="padding: 8px 15px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;" disabled>Submit</button>
            </div>
        `;

        // Event listener to close the dialog
        dialog.querySelector('#dialog-cancel-btn').onclick = () => {
            document.body.removeChild(overlay);
        };

        const select = dialog.querySelector('#cancel-reason-select');
        const submitBtn = dialog.querySelector('#dialog-submit-btn');
        const otherGroup = dialog.querySelector('#other-reason-group');
        const otherInput = dialog.querySelector('#other-reason-input');

        // Logic to toggle 'Other' input field and enable/disable submit button
        select.onchange = () => {
            const selectedValue = select.value;
            const isOther = selectedValue === "Other (Please specify)";
            
            // Show/Hide the 'Other' textarea
            otherGroup.style.display = isOther ? 'block' : 'none';
            
            if (isOther) {
                // If 'Other' is selected, check if textarea has content
                submitBtn.disabled = !otherInput.value.trim();
            } else {
                // If a predefined reason is selected, enable submit
                submitBtn.disabled = !selectedValue;
            }
        };
        
        // Check input length for the 'Other' reason field
        otherInput.oninput = () => {
            if (select.value === "Other (Please specify)") {
                submitBtn.disabled = !otherInput.value.trim();
            }
        };

        // Submission logic
        submitBtn.onclick = () => {
            let finalReason = select.value;

            if (finalReason === "Other (Please specify)") {
                // Prepend "Other:" to the user-specified reason
                finalReason = "Other: " + otherInput.value.trim();
            }

            if (finalReason) {
                document.body.removeChild(overlay);
                // Call the existing function to send data to the server
                processOrderAction(orderId, 'canceled', finalReason);
            } else {
                alert("Please select a valid cancellation reason.");
            }
        };

        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
    }
    
    // Original Event Listener: Handles all buttons (Accept, Cancel, etc.)
    document.addEventListener('click', (event) => {
        const target = event.target;
        
        // Handle Accept button (unchanged)
        if (target.classList.contains('btn-accept')) {
            const orderId = target.dataset.orderId;
            if (confirm(`Are you sure you want to accept order #${orderId}? This will move the order to 'accepted' status.`)) {
                processOrderAction(orderId, 'accepted');
            }
        }

        // Handle Cancel button: Now calls the custom dialog
        if (target.classList.contains('btn-cancel')) {
            const orderId = target.dataset.orderId;
            showCancellationDialog(orderId);
        }
        
        // ... (other status buttons logic remains here)
        
    });

    // Original AJAX function: Sends data to update_order.php
    function processOrderAction(orderId, newStatus, reason ) {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', newStatus); 
        if (reason) {
            // Sends the 'cancellation_reason' parameter
            formData.append('cancellation_reason', reason);
        }

        fetch('update_order.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                if (typeof window.fetchOrders === 'function') {
                    window.fetchOrders(); 
                } else {
                    console.warn('fetchOrders function not found. Status will update on next poll.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});