import { Router } from 'express';

const router = Router();

// Get all projects
router.get('/', (req, res) => {
  // Mock data
  res.json({
    projects: [
      { id: '1', name: 'My First Project', createdAt: new Date() },
    ],
  });
});

// Create project
router.post('/', (req, res) => {
  const { name, description } = req.body;
  res.json({
    success: true,
    project: { id: '1', name, description },
  });
});

// Get project by ID
router.get('/:id', (req, res) => {
  res.json({
    project: {
      id: req.params.id,
      name: 'My Project',
      pages: [],
    },
  });
});

// Delete project
router.delete('/:id', (req, res) => {
  res.json({ success: true });
});

export default router;
