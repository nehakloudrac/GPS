angular.module 'gps.dashboard'
.constant 'dashboardTutorial', [
    title: "Thank you for joining GPS"
    key: "dashboard"
    items: [
      {type: 'text', text: "From your personalized Dashboard, you can view and edit your profile, track your activity, and manage account preferences."}
      {type: 'text', text: "Take a few minutes to review our tutorial and familiarize yourself with the Dashboard features. To skip the tutorial, click \"Close\"."}
    ]
  ,
    title: "Dashboard Announcements"
    key: "banner"
    items: [
      {type: 'text', text: "Your Dashboard will greet you in a different language each time you log in. GPS will post any important announcements in this area of your Dashboard."}
      {type: 'img', src: "/public/images/tutorials/db-greeting.png"}
    ]
  ,  
    title: "Navigation"
    key: "banner-buttons"
    items: [
      {type: 'text', text: "Your Dashboard contains multiple tabs."}
      {type: 'img', src: "/public/images/tutorials/db-tabs.png"}
      {type: 'text', text: "Click <em>Dashboard</em> to view the latest announcements and news from GPS and the completeness of your profile."}
      {type: 'text', text: "Click <em>Profile</em> to create or update your profile."}
      {type: 'text', text: "Click <em>Resources</em> for reading recommendations, interview tips, and other career-oriented tools and services. Follow GPS on Twitter here."}
      {type: 'text', text: "Click <em>Account</em> to update your contact information, upload your photo, and more. Keep your job-searching status up-to-date so that we know whether to contact you when employers show interest."}
    ]
  ,
    title: "Help Button"
    key: "help"
    items: [
      {type: 'text', text: 'You can “leave us a message” at any time by clicking the button located at the bottom right of your window throughout the GPS website. Please contact us if you need assistance or would like to provide helpful feedback.'}
    ]
]
