const UserAccount = require('../entities/UserAccount');
const UserProfile = require('../entities/UserProfile');
const RoleDefinition = require('../entities/RoleDefinition');
const PinRequest = require('../entities/PinRequest');
const ShortlistItem = require('../entities/ShortlistItem');
const CsrHistoryEntry = require('../entities/CsrHistoryEntry');
const PinMetric = require('../entities/PinMetric');
const PinMatch = require('../entities/PinMatch');
const ServiceCategory = require('../entities/ServiceCategory');
const Report = require('../entities/Report');
const VolunteerOpportunity = require('../entities/VolunteerOpportunity');

class VolunteerPlatformDataStore {
  constructor() {
    this.requestStatuses = {
      pending: { label: 'Awaiting Match', className: 'status-pending' },
      matched: { label: 'Matched', className: 'status-matched' },
      completed: { label: 'Completed', className: 'status-completed' },
      active: { label: 'Active', className: 'status-active' },
      suspended: { label: 'Suspended', className: 'status-suspended' }
    };

    this.roles = [
      new RoleDefinition({
        name: 'User Administrator',
        highlights: [
          'Create, update, and suspend user accounts and profiles',
          'Search for account details and permission assignments',
          'Monitor access to keep the platform secure'
        ]
      }),
      new RoleDefinition({
        name: 'CSR Representative',
        highlights: [
          'Browse, filter, and shortlist volunteer opportunities',
          'Review request details and track completion history',
          'Report back after fulfilling a service'
        ]
      }),
      new RoleDefinition({
        name: 'Person in Need (PIN)',
        highlights: [
          'Submit, update, and cancel help requests',
          'Monitor visibility metrics such as views and shortlist counts',
          'Review historical matches and connected CSR partners'
        ]
      }),
      new RoleDefinition({
        name: 'Platform Manager',
        highlights: [
          'Maintain the catalogue of service categories',
          'Publish daily, weekly, and monthly activity reports',
          'Analyse platform performance for continuous improvement'
        ]
      })
    ];

    this.pinRequests = [
      new PinRequest({
        title: 'Hospital Follow-up Companion',
        category: 'Medical Escort',
        status: 'pending',
        views: 42,
        favorites: 6,
        submittedAt: '2025-02-12'
      }),
      new PinRequest({
        title: 'Wheelchair Maintenance Support',
        category: 'Mobility Assistance',
        status: 'matched',
        views: 38,
        favorites: 9,
        submittedAt: '2025-02-10'
      }),
      new PinRequest({
        title: 'Family Meal Preparation Help',
        category: 'Daily Care',
        status: 'completed',
        views: 55,
        favorites: 14,
        submittedAt: '2025-01-30'
      })
    ];

    this.csrShortlist = [
      new ShortlistItem({
        title: 'Community Rehab Exercise Coach',
        desc: 'Service date: 2025-03-03 | Category: Health Support'
      }),
      new ShortlistItem({
        title: 'Elderly Clinic Companion',
        desc: 'Service date: 2025-02-28 | Category: Medical Escort'
      })
    ];

    this.csrHistory = [
      new CsrHistoryEntry({
        title: 'Senior Wellness Check-ins',
        category: 'Health Support',
        serviceDate: '2025-01-18',
        duration: '4 hours'
      }),
      new CsrHistoryEntry({
        title: 'Community Library Sorting',
        category: 'Community Building',
        serviceDate: '2024-12-05',
        duration: '3 hours'
      }),
      new CsrHistoryEntry({
        title: 'Youth Study Mentoring',
        category: 'Education Support',
        serviceDate: '2024-11-22',
        duration: '2 hours'
      })
    ];

    this.pinMetrics = [
      new PinMetric({
        title: 'Total Views Today',
        value: '128',
        description: 'Up 12% compared with yesterday'
      }),
      new PinMetric({
        title: 'Total Shortlists Today',
        value: '37',
        description: 'Nine CSR representatives added requests to their shortlist'
      }),
      new PinMetric({
        title: 'Match Success Rate',
        value: '82%',
        description: '45 matches completed successfully this month'
      })
    ];

    this.pinMatches = [
      new PinMatch({
        service: 'Post-surgery Clinic Support',
        csr: 'Lena Torres',
        completedAt: '2025-01-12',
        feedback: 'Outstanding punctuality and empathy throughout the visit'
      }),
      new PinMatch({
        service: 'Accessible Transport Escort',
        csr: 'Kai Morgan',
        completedAt: '2024-12-20',
        feedback: 'Coordinated the hospital check smoothly and safely'
      }),
      new PinMatch({
        service: 'Home Nutrition Coaching',
        csr: 'Riley Chen',
        completedAt: '2024-11-30',
        feedback: 'Improved meal planning with practical weekly tips'
      })
    ];

    this.serviceCategories = [
      new ServiceCategory({ name: 'Medical Escort', status: 'active' }),
      new ServiceCategory({ name: 'Emotional Support', status: 'active' }),
      new ServiceCategory({ name: 'Emergency Response', status: 'suspended' })
    ];

    this.userAccounts = [
      new UserAccount({
        username: 'admin.reed',
        displayName: 'Morgan Reed',
        role: 'User Administrator',
        status: 'active',
        lastLogin: '2025-02-14 09:30',
        password: 'admin123'
      }),
      new UserAccount({
        username: 'csr.wilson',
        displayName: 'Harper Wilson',
        role: 'CSR Representative',
        status: 'active',
        lastLogin: '2025-02-13 18:05',
        password: 'csr12345'
      }),
      new UserAccount({
        username: 'pin.jordan',
        displayName: 'Avery Jordan',
        role: 'Person in Need (PIN)',
        status: 'suspended',
        lastLogin: '2024-12-22 14:12',
        password: 'pin12345'
      })
    ];

    this.userProfiles = [
      new UserProfile({
        name: 'User Administrator',
        description: 'Maintains secure access for every user account and profile.',
        permissions: [
          'Create or update accounts',
          'Assign role permissions',
          'Suspend or reinstate access'
        ],
        status: 'active'
      }),
      new UserProfile({
        name: 'CSR Representative',
        description: 'Coordinates corporate volunteers with incoming service requests.',
        permissions: [
          'Browse and filter requests',
          'Maintain a shortlist',
          'Record service outcomes'
        ],
        status: 'active'
      }),
      new UserProfile({
        name: 'Person in Need (PIN)',
        description: 'Submits and monitors help requests and engagement metrics.',
        permissions: [
          'Create or update requests',
          'View visibility analytics',
          'Review completed matches'
        ],
        status: 'active'
      }),
      new UserProfile({
        name: 'Platform Manager',
        description: 'Curates service categories and analyses platform performance.',
        permissions: [
          'Manage service categories',
          'Generate operational reports',
          'Publish platform notices'
        ],
        status: 'active'
      })
    ];

    this.volunteerOpportunities = [
      new VolunteerOpportunity({
        title: 'Clinic Companion Volunteer',
        category: 'Medical Escort',
        location: 'Downtown Community Hospital',
        schedule: '2025-02-18',
        status: 'open'
      }),
      new VolunteerOpportunity({
        title: 'Wheelchair Transit Assistant',
        category: 'Mobility Assistance',
        location: 'Riverside Senior Centre',
        schedule: '2025-02-20',
        status: 'open'
      }),
      new VolunteerOpportunity({
        title: 'Rehabilitation Exercise Guide',
        category: 'Health Support',
        location: 'Harbourview Wellness Hub',
        schedule: '2025-03-02',
        status: 'filled'
      })
    ];

    this.reports = [
      new Report({
        title: 'Daily Report',
        highlights: [
          'New help requests submitted today: 18',
          'Matches confirmed today: 12'
        ]
      }),
      new Report({
        title: 'Weekly Report',
        highlights: [
          'CSR representatives engaged this week: 54',
          'Volunteer services completed this week: 68'
        ]
      }),
      new Report({
        title: 'Monthly Report',
        highlights: [
          'New PIN registrations this month: 35',
          'Average satisfaction rating this month: 4.8 / 5'
        ]
      })
    ];
  }

  getRequestStatuses() {
    return this.requestStatuses;
  }

  getRoles() {
    return this.roles.map((role) => role.toJSON());
  }

  getPinRequests() {
    return this.pinRequests.map((request) => request.toJSON());
  }

  getCsrShortlist() {
    return this.csrShortlist.map((item) => item.toJSON());
  }

  getCsrHistory() {
    return this.csrHistory.map((entry) => entry.toJSON());
  }

  getPinMetrics() {
    return this.pinMetrics.map((metric) => metric.toJSON());
  }

  getPinMatches() {
    return this.pinMatches.map((match) => match.toJSON());
  }

  getServiceCategories() {
    return this.serviceCategories.map((category) => category.toJSON());
  }

  getReports() {
    return this.reports.map((report) => report.toJSON());
  }

  getVolunteerOpportunities() {
    return this.volunteerOpportunities.map((opportunity) => opportunity.toJSON());
  }

  authenticateUser(username, password, role) {
    return this.userAccounts.find(
      (account) =>
        account.username === username &&
        account.password === password &&
        (!role || account.role === role)
    );
  }

  addUserAccount(account) {
    const entity = account instanceof UserAccount ? account : new UserAccount(account);
    this.userAccounts.push(entity);
    return entity;
  }

  updateUserAccount(username, updates) {
    const target = this.userAccounts.find((account) => account.username === username);
    if (!target) return null;
    target.update(updates);
    return target;
  }

  getUserAccount(username) {
    return this.userAccounts.find((account) => account.username === username);
  }

  searchUserAccounts(keyword) {
    return this.userAccounts
      .filter((account) => account.matchesKeyword(keyword))
      .map((account) => account.toJSON());
  }

  addUserProfile(profile) {
    const entity = profile instanceof UserProfile ? profile : new UserProfile(profile);
    this.userProfiles.push(entity);
    return entity;
  }

  updateUserProfile(name, updates) {
    const target = this.userProfiles.find((profile) => profile.name === name);
    if (!target) return null;
    target.update(updates);
    return target;
  }

  searchUserProfiles(keyword) {
    return this.userProfiles
      .filter((profile) => profile.matchesKeyword(keyword))
      .map((profile) => profile.toJSON());
  }
}

const dataStore = new VolunteerPlatformDataStore();

module.exports = {
  VolunteerPlatformDataStore,
  dataStore
};
