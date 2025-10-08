const request = require('supertest');
const appInstance = require('../server');

describe('View rendering', () => {
  it('redirects anonymous visitors from the dashboard to the login page', async () => {
    const agent = request.agent(appInstance.app);
    const response = await agent.get('/dashboard');

    expect(response.status).toBe(302);
    expect(response.headers.location).toBe('/login');

    const loginPage = await agent.get('/login');
    expect(loginPage.text).toContain('Please sign in before accessing the dashboard.');
  });

  it('renders the admin dashboard with administrator workspace', async () => {
    const agent = request.agent(appInstance.app);

    await agent
      .post('/login')
      .type('form')
      .send({
        role: 'User Administrator',
        username: 'admin.reed',
        password: 'admin123'
      });

    const response = await agent.get('/dashboard');

    expect(response.status).toBe(200);
    expect(response.text).toContain('User Administrator Workspace');
    expect(response.text).toContain('Create User Account');
  });
});
