<?php
    $brandSettings = \App\Models\BrandSettings::getSettings();
?>

<!-- Chatbot Widget -->
<div id="chatbot-widget" class="fixed z-50" style="bottom: 1.5rem; right: 1.5rem;">
    <!-- Chat Button -->
    <button id="chatbot-toggle" class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 cursor-move" style="background-color: <?php echo e($brandSettings->primary_color); ?>;">
        <svg id="chatbot-icon" class="size-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <svg id="chatbot-close" class="size-6 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="hidden absolute bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden" style="width: 20rem; height: 24rem; z-index: 100;">
        <!-- Header -->
        <div class="p-4 text-white cursor-move" style="background-color: <?php echo e($brandSettings->primary_color); ?>;">
            <h3 class="font-semibold">Need Help?</h3>
            <p class="text-xs opacity-90">Ask me anything about the app</p>
        </div>

        <!-- Messages -->
        <div id="chatbot-messages" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div class="flex gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0" style="background-color: <?php echo e($brandSettings->primary_color); ?>;">
                    AI
                </div>
                <div class="bg-slate-100 rounded-2xl rounded-tl-none p-3 max-w-[80%]">
                    <p class="text-sm text-slate-700">Hi! I'm here to help you navigate the app. What would you like to know?</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="p-3 border-t border-slate-200">
            <div class="flex flex-wrap gap-2">
                <button onclick="sendQuickMessage('How do I add a menu item?')" class="text-xs px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Add menu item</button>
                <button onclick="sendQuickMessage('How do I manage inventory?')" class="text-xs px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Manage inventory</button>
                <button onclick="sendQuickMessage('How do I process payments?')" class="text-xs px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Process payments</button>
            </div>
        </div>

        <!-- Input -->
        <div class="p-3 border-t border-slate-200 flex gap-2">
            <input type="text" id="chatbot-input" placeholder="Type your question..." class="flex-1 px-4 py-2 rounded-full border border-slate-300 focus:outline-none focus:border-slate-500 text-sm" onkeypress="if(event.key === 'Enter') sendMessage()">
            <button onclick="sendMessage()" class="w-10 h-10 rounded-full flex items-center justify-center text-white transition-colors" style="background-color: <?php echo e($brandSettings->primary_color); ?>;">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
let chatbotOpen = false;
let isDragging = false;
let dragOffset = { x: 0, y: 0 };
let currentPosition = { x: 0, y: 0 };
let dragStartTime = 0;

function toggleChatbot() {
    chatbotOpen = !chatbotOpen;
    const window = document.getElementById('chatbot-window');
    const icon = document.getElementById('chatbot-icon');
    const close = document.getElementById('chatbot-close');
    const widget = document.getElementById('chatbot-widget');
    
    if (chatbotOpen) {
        // Simple positioning: show window above the button
        window.style.bottom = '5.5rem';
        window.style.right = '0';
        window.style.top = 'auto';
        window.style.left = 'auto';
        window.style.zIndex = '100';
        
        window.classList.remove('hidden');
        icon.classList.add('hidden');
        close.classList.remove('hidden');
        
        console.log('Chatbot window opened');
    } else {
        window.classList.add('hidden');
        window.style.bottom = '';
        window.style.right = '';
        window.style.top = '';
        window.style.left = '';
        icon.classList.remove('hidden');
        close.classList.add('hidden');
        
        console.log('Chatbot window closed');
    }
}

function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    if (!message) return;
    
    addUserMessage(message);
    input.value = '';
    
    setTimeout(() => {
        const response = getAIResponse(message);
        addBotMessage(response);
    }, 500);
}

function sendQuickMessage(message) {
    addUserMessage(message);
    setTimeout(() => {
        const response = getAIResponse(message);
        addBotMessage(response);
    }, 500);
}

function addUserMessage(message) {
    const messages = document.getElementById('chatbot-messages');
    const div = document.createElement('div');
    div.className = 'flex gap-2 justify-end';
    div.innerHTML = `
        <div class="rounded-2xl rounded-tr-none p-3 max-w-[80%]" style="background-color: <?php echo e($brandSettings->primary_color); ?>;">
            <p class="text-sm text-white">${message}</p>
        </div>
    `;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
}

function addBotMessage(message) {
    const messages = document.getElementById('chatbot-messages');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0" style="background-color: <?php echo e($brandSettings->primary_color); ?>;">
            AI
        </div>
        <div class="bg-slate-100 rounded-2xl rounded-tl-none p-3 max-w-[80%]">
            <p class="text-sm text-slate-700">${message}</p>
        </div>
    `;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
}

function getAIResponse(message) {
    const lowerMessage = message.toLowerCase();
    
    if (lowerMessage.includes('menu') && lowerMessage.includes('add')) {
        return "To add a menu item: Go to Menu > Click 'Add item' > Fill in the details (name, description, price, category) > Select ingredients if needed > Upload an image > Click 'Add item'.";
    } else if (lowerMessage.includes('menu') && lowerMessage.includes('edit')) {
        return "To edit a menu item: Go to Menu > Click 'Edit' on the item you want to modify > Make your changes > Click 'Update item'.";
    } else if (lowerMessage.includes('inventory')) {
        return "To manage inventory: Go to Inventory > You can view stock levels, add stock movements, manage bulk purchases, and organize stock categories. Use the Supplies section for ingredient management.";
    } else if (lowerMessage.includes('payment') || lowerMessage.includes('pay')) {
        return "To process payments: Go to Pay > Select an active order > Click 'Pay Order' > Choose payment method (Cash, Card, or QR) > Confirm. The receipt will be generated automatically.";
    } else if (lowerMessage.includes('order') || lowerMessage.includes('kitchen')) {
        return "For kitchen orders: Go to Kitchen to see pending orders. Use Pickup Station to manage completed orders ready for pickup. You can mark items as ready and close orders when completed.";
    } else if (lowerMessage.includes('staff') || lowerMessage.includes('employee')) {
        return "To manage staff: Go to Staff (if you have permission) > You can add/edit employees, manage roles and permissions, track attendance, and process payroll.";
    } else if (lowerMessage.includes('report')) {
        return "To view reports: Go to Reports > You can generate sales reports, export to CSV/PDF, and print reports. Reports show revenue, orders, and other key metrics.";
    } else if (lowerMessage.includes('brand') || lowerMessage.includes('logo') || lowerMessage.includes('settings')) {
        return "To customize brand settings: Go to Brand (if you're Super Admin) > You can change the app name, logo, colors, tagline, and other branding elements.";
    } else {
        return "I can help you with menu management, inventory, payments, kitchen orders, staff management, reports, and brand settings. What would you like to know more about?";
    }
}

// Drag functionality
const chatbotWidget = document.getElementById('chatbot-widget');
const chatbotToggle = document.getElementById('chatbot-toggle');
const chatbotWindow = document.getElementById('chatbot-window');
const chatbotHeader = chatbotWindow.querySelector('.cursor-move');

chatbotToggle.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    toggleChatbot();
});

chatbotToggle.addEventListener('mousedown', function(e) {
    e.preventDefault();
    dragStartTime = Date.now();
    isDragging = false;
    
    const rect = chatbotWidget.getBoundingClientRect();
    dragOffset.x = e.clientX - rect.left;
    dragOffset.y = e.clientY - rect.top;
    
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
});

chatbotHeader.addEventListener('mousedown', function(e) {
    e.preventDefault();
    dragStartTime = Date.now();
    isDragging = false;
    
    const rect = chatbotWidget.getBoundingClientRect();
    dragOffset.x = e.clientX - rect.left;
    dragOffset.y = e.clientY - rect.top;
    
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
});

function drag(e) {
    isDragging = true;
    
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    
    let newX = e.clientX - dragOffset.x;
    let newY = e.clientY - dragOffset.y;
    
    // Keep within bounds
    newX = Math.max(0, Math.min(newX, windowWidth - chatbotWidget.offsetWidth));
    newY = Math.max(0, Math.min(newY, windowHeight - chatbotWidget.offsetHeight));
    
    currentPosition.x = newX;
    currentPosition.y = newY;
    
    chatbotWidget.style.left = newX + 'px';
    chatbotWidget.style.top = newY + 'px';
    chatbotWidget.style.right = 'auto';
    chatbotWidget.style.bottom = 'auto';
}

function stopDrag() {
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
    setTimeout(() => {
        isDragging = false;
    }, 100);
}
</script>
<?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/partials/chatbot.blade.php ENDPATH**/ ?>