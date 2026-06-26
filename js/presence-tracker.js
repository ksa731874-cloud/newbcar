/**
 * B-CARE Real-time Visitor Tracker
 * Uses Pusher Presence Channels for precise activity monitoring.
 */

(function() {
    // Check if Pusher is loaded
    if (typeof Pusher === 'undefined') {
        console.error('Pusher SDK is required for tracking.');
        return;
    }

    // Config (should match config.php)
    const PUSHER_KEY = '4a9de0023f3255d461d9';
    const PUSHER_CLUSTER = 'ap2';
    const AUTH_ENDPOINT = 'pusher-auth.php';

    // Initialize Pusher with Auth
    const pusher = new Pusher(PUSHER_KEY, {
        cluster: PUSHER_CLUSTER,
        authEndpoint: AUTH_ENDPOINT,
        encrypted: true
    });

    const channelName = 'presence-bcare';
    const presenceChannel = pusher.subscribe(channelName);

    // Track current page
    const currentPageName = document.title || window.location.pathname;

    presenceChannel.bind('pusher:subscription_succeeded', (members) => {
        console.log('Connected to presence channel. Members:', members.count);
    });

    presenceChannel.bind('pusher:member_added', (member) => {
        console.log('Member joined:', member.id, member.info);
    });

    presenceChannel.bind('pusher:member_removed', (member) => {
        console.log('Member left:', member.id);
    });

    // Handle Page Visibility (Away/Back)
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            // Optional: trigger an event to say user is away
        } else {
            // User is back
        }
    });

    // Listen for remote redirection from Admin
    presenceChannel.bind('remote_redirect', (data) => {
        if (data.userId === window.userIdFromSession || data.userId === ('visitor-' + window.userIdFromSession)) {
            console.log('Remote redirect received to:', data.page);
            window.location.href = data.page;
        }
    });

    // Export for manual usage if needed
    window.BcareTracker = {
        pusher: pusher,
        channel: presenceChannel,
        currentPage: currentPageName
    };

    console.log('B-CARE Tracker Initialized for page:', currentPageName);

})();
