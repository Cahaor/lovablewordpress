import { Router } from 'express';

const router = Router();

// Connect WordPress site
router.post('/connect', (req, res) => {
  const { url, username, applicationPassword } = req.body;
  
  // Mock connection
  res.json({
    success: true,
    message: 'WordPress site connected',
    site: { url, name: 'My WordPress Site' },
  });
});

// Get connected sites
router.get('/sites', (req, res) => {
  res.json({
    sites: [
      { id: '1', url: 'https://example.com', name: 'Example Site' },
    ],
  });
});

// Push to WordPress
router.post('/push', (req, res) => {
  const { siteId, components } = req.body;
  
  res.json({
    success: true,
    message: 'Components pushed to WordPress',
  });
});

// Sync from Lovable
router.post('/sync', (req, res) => {
  res.json({
    success: true,
    message: 'Synced with Lovable',
  });
});

export default router;
