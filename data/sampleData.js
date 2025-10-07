// Demo data: centralised static content that can later be replaced with real data sources.
const requestStatuses = {
  pending: { label: "Awaiting Match", className: "status-pending" },
  matched: { label: "Matched", className: "status-matched" },
  completed: { label: "Completed", className: "status-completed" },
  active: { label: "Active", className: "status-active" },
  suspended: { label: "Suspended", className: "status-suspended" }
};

const roles = [
  {
    name: "User Administrator",
    highlights: [
      "Create, update, and suspend user accounts and profiles",
      "Search for account details and permission assignments",
      "Monitor access to keep the platform secure"
    ]
  },
  {
    name: "CSR Representative",
    highlights: [
      "Browse, filter, and shortlist volunteer opportunities",
      "Review request details and track completion history",
      "Report back after fulfilling a service"
    ]
  },
  {
    name: "Person in Need (PIN)",
    highlights: [
      "Submit, update, and cancel help requests",
      "Monitor visibility metrics such as views and shortlist counts",
      "Review historical matches and connected CSR partners"
    ]
  },
  {
    name: "Platform Manager",
    highlights: [
      "Maintain the catalogue of service categories",
      "Publish daily, weekly, and monthly activity reports",
      "Analyse platform performance for continuous improvement"
    ]
  }
];

const pinRequests = [
  {
    title: "Hospital Follow-up Companion",
    category: "Medical Escort",
    status: "pending",
    views: 42,
    favorites: 6,
    submittedAt: "2025-02-12"
  },
  {
    title: "Wheelchair Maintenance Support",
    category: "Mobility Assistance",
    status: "matched",
    views: 38,
    favorites: 9,
    submittedAt: "2025-02-10"
  },
  {
    title: "Family Meal Preparation Help",
    category: "Daily Care",
    status: "completed",
    views: 55,
    favorites: 14,
    submittedAt: "2025-01-30"
  }
];

const csrShortlist = [
  {
    title: "Community Rehab Exercise Coach",
    desc: "Service date: 2025-03-03 | Category: Health Support"
  },
  {
    title: "Elderly Clinic Companion",
    desc: "Service date: 2025-02-28 | Category: Medical Escort"
  }
];

const csrHistory = [
  {
    title: "Senior Wellness Check-ins",
    category: "Health Support",
    serviceDate: "2025-01-18",
    duration: "4 hours"
  },
  {
    title: "Community Library Sorting",
    category: "Community Building",
    serviceDate: "2024-12-05",
    duration: "3 hours"
  },
  {
    title: "Youth Study Mentoring",
    category: "Education Support",
    serviceDate: "2024-11-22",
    duration: "2 hours"
  }
];

const pinMetrics = [
  {
    title: "Total Views Today",
    value: "128",
    description: "Up 12% compared with yesterday"
  },
  {
    title: "Total Shortlists Today",
    value: "37",
    description: "Nine CSR representatives added requests to their shortlist"
  },
  {
    title: "Match Success Rate",
    value: "82%",
    description: "45 matches completed successfully this month"
  }
];

const pinMatches = [
  {
    service: "Post-surgery Clinic Support",
    csr: "Lena Torres",
    completedAt: "2025-01-12",
    feedback: "Outstanding punctuality and empathy throughout the visit"
  },
  {
    service: "Accessible Transport Escort",
    csr: "Kai Morgan",
    completedAt: "2024-12-20",
    feedback: "Coordinated the hospital check smoothly and safely"
  },
  {
    service: "Home Nutrition Coaching",
    csr: "Riley Chen",
    completedAt: "2024-11-30",
    feedback: "Improved meal planning with practical weekly tips"
  }
];

const serviceCategories = [
  { name: "Medical Escort", status: "active" },
  { name: "Emotional Support", status: "active" },
  { name: "Emergency Response", status: "suspended" }
];

const userAccounts = [
  {
    username: "admin.reed",
    displayName: "Morgan Reed",
    role: "User Administrator",
    status: "active",
    lastLogin: "2025-02-14 09:30",
    password: "admin123"
  },
  {
    username: "csr.wilson",
    displayName: "Harper Wilson",
    role: "CSR Representative",
    status: "active",
    lastLogin: "2025-02-13 18:05",
    password: "csr12345"
  },
  {
    username: "pin.jordan",
    displayName: "Avery Jordan",
    role: "Person in Need (PIN)",
    status: "suspended",
    lastLogin: "2024-12-22 14:12",
    password: "pin12345"
  }
];

const userProfiles = [
  {
    name: "User Administrator",
    description: "Maintains secure access for every user account and profile.",
    permissions: [
      "Create or update accounts",
      "Assign role permissions",
      "Suspend or reinstate access"
    ],
    status: "active"
  },
  {
    name: "CSR Representative",
    description: "Coordinates corporate volunteers with incoming service requests.",
    permissions: [
      "Browse and filter requests",
      "Maintain a shortlist",
      "Record service outcomes"
    ],
    status: "active"
  },
  {
    name: "Person in Need (PIN)",
    description: "Submits and monitors help requests and engagement metrics.",
    permissions: [
      "Create or update requests",
      "View visibility analytics",
      "Review completed matches"
    ],
    status: "active"
  },
  {
    name: "Platform Manager",
    description: "Curates service categories and analyses platform performance.",
    permissions: [
      "Manage service categories",
      "Generate operational reports",
      "Publish platform notices"
    ],
    status: "active"
  }
];

const volunteerOpportunities = [
  {
    title: "Clinic Companion Volunteer",
    category: "Medical Escort",
    location: "Downtown Community Hospital",
    schedule: "2025-02-18",
    status: "open"
  },
  {
    title: "Wheelchair Transit Assistant",
    category: "Mobility Assistance",
    location: "Riverside Senior Centre",
    schedule: "2025-02-20",
    status: "open"
  },
  {
    title: "Rehabilitation Exercise Guide",
    category: "Health Support",
    location: "Harbourview Wellness Hub",
    schedule: "2025-03-02",
    status: "filled"
  }
];

const reports = [
  {
    title: "Daily Report",
    highlights: [
      "New help requests submitted today: 18",
      "Matches confirmed today: 12"
    ]
  },
  {
    title: "Weekly Report",
    highlights: [
      "CSR representatives engaged this week: 54",
      "Volunteer services completed this week: 68"
    ]
  },
  {
    title: "Monthly Report",
    highlights: [
      "New PIN registrations this month: 35",
      "Average satisfaction rating this month: 4.8 / 5"
    ]
  }
];

// Data helpers: lightweight in-memory operations to simulate CRUD behaviour.
function authenticateUser(username, password, role) {
  return userAccounts.find(
    (account) =>
      account.username === username &&
      account.password === password &&
      (!role || account.role === role)
  );
}

function addUserAccount(account) {
  userAccounts.push(account);
  return account;
}

function updateUserAccount(username, updates) {
  const target = userAccounts.find((account) => account.username === username);
  if (!target) return null;
  Object.assign(target, updates);
  return target;
}

function searchUserAccounts(keyword) {
  if (!keyword) return userAccounts;
  const lower = keyword.toLowerCase();
  return userAccounts.filter(
    (account) =>
      account.username.toLowerCase().includes(lower) ||
      account.displayName.toLowerCase().includes(lower) ||
      account.role.toLowerCase().includes(lower)
  );
}

function getUserAccount(username) {
  return userAccounts.find((account) => account.username === username);
}

function addUserProfile(profile) {
  userProfiles.push(profile);
  return profile;
}

function updateUserProfile(name, updates) {
  const target = userProfiles.find((profile) => profile.name === name);
  if (!target) return null;
  Object.assign(target, updates);
  return target;
}

function searchUserProfiles(keyword) {
  if (!keyword) return userProfiles;
  const lower = keyword.toLowerCase();
  return userProfiles.filter(
    (profile) =>
      profile.name.toLowerCase().includes(lower) ||
      profile.description.toLowerCase().includes(lower)
  );
}

module.exports = {
  requestStatuses,
  roles,
  pinRequests,
  csrShortlist,
  csrHistory,
  pinMetrics,
  pinMatches,
  serviceCategories,
  reports,
  userAccounts,
  userProfiles,
  volunteerOpportunities,
  authenticateUser,
  addUserAccount,
  updateUserAccount,
  getUserAccount,
  searchUserAccounts,
  addUserProfile,
  updateUserProfile,
  searchUserProfiles
};
