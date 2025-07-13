# Dashboard Design Specifications

## Overall Design Philosophy
- **Modern & Clean**: Use a clean, modern interface with ample white space
- **Responsive**: Fully responsive design that works on all devices
- **Intuitive**: Easy to navigate with clear visual hierarchy
- **Consistent**: Maintain consistent design patterns across all dashboard views
- **Accessible**: Follow accessibility best practices (WCAG 2.1)
- **Brand Aligned**: Use colors and styling that align with the Courts Management System

## Color Scheme
- **Primary**: #0d6efd (Bootstrap primary blue)
- **Secondary**: #6c757d (Bootstrap secondary gray)
- **Success**: #198754 (Bootstrap success green)
- **Danger**: #dc3545 (Bootstrap danger red)
- **Warning**: #ffc107 (Bootstrap warning yellow)
- **Info**: #0dcaf0 (Bootstrap info light blue)
- **Light**: #f8f9fa (Bootstrap light gray)
- **Dark**: #212529 (Bootstrap dark gray)
- **White**: #ffffff
- **Accent**: #6f42c1 (Purple for highlighting important elements)

## Typography
- **Primary Font**: System font stack (same as Bootstrap 5)
- **Headings**: Slightly bolder weight for better hierarchy
- **Body Text**: 16px base size for readability
- **Line Height**: 1.5 for optimal readability

## Layout Structure
1. **Top Navigation Bar**
   - Logo/Brand
   - Search bar
   - Notifications icon
   - Messages icon
   - User profile dropdown
   - Theme toggle (light/dark mode)

2. **Sidebar Navigation**
   - User profile summary
   - Main navigation menu (role-specific)
   - Quick actions
   - Collapse/expand toggle
   - Logout option

3. **Main Content Area**
   - Breadcrumb navigation
   - Page title and actions
   - Content cards/widgets
   - Data tables/forms
   - Pagination controls

4. **Footer**
   - Copyright information
   - Version information
   - Quick links

## Common UI Components
1. **Cards**
   - Statistics cards
   - Information cards
   - Action cards
   - List cards

2. **Tables**
   - Data tables with sorting/filtering
   - Responsive tables
   - Action buttons in tables

3. **Forms**
   - Input fields with validation
   - Dropdowns and select menus
   - Date pickers
   - File uploads
   - Form wizards for complex processes

4. **Charts & Visualizations**
   - Bar charts
   - Line charts
   - Pie/Donut charts
   - Calendar views
   - Kanban boards

5. **Modals & Dialogs**
   - Confirmation dialogs
   - Form modals
   - Information modals
   - Alert modals

6. **Notifications**
   - Toast notifications
   - Alert banners
   - Badge indicators

## Role-Specific Dashboard Layouts

### Administrator Dashboard
- **Layout**: Full-width statistics at top, 3-column widget layout below
- **Key Widgets**: 
  - System health metrics
  - New user registrations
  - Recent activities
  - User management quick access
  - System alerts

### Hon. Judge Dashboard
- **Layout**: Calendar view at top, 2-column layout below
- **Key Widgets**:
  - Today's cases
  - Pending judgments
  - Recent case activities
  - Statistics on case resolution

### The Registrar Dashboard
- **Layout**: Task-oriented layout with action cards
- **Key Widgets**:
  - Case registration stats
  - Fee collection summary
  - Document management
  - Court scheduling

### Interpreter Dashboard
- **Layout**: Schedule-focused with language resources
- **Key Widgets**:
  - Upcoming interpretation assignments
  - Language resources
  - Case notes

### Other Staff Dashboard
- **Layout**: Task-oriented with communication focus
- **Key Widgets**:
  - Task assignments
  - Communication center
  - Document access

### Lawyer Dashboard
- **Layout**: Client-case focused with legal tools
- **Key Widgets**:
  - Client cases
  - Court schedules
  - Document submission
  - Legal research

### Police Dashboard
- **Layout**: Warrant and case tracking focused
- **Key Widgets**:
  - Active warrants
  - Case status updates
  - Evidence submission
  - Court appearances

## Mobile Considerations
- Collapsible sidebar
- Simplified navigation
- Touch-friendly interface
- Optimized tables and forms
- Reduced information density

## Accessibility Features
- High contrast mode
- Keyboard navigation
- Screen reader compatibility
- Focus indicators
- Alternative text for images
- Semantic HTML structure

## Interactive Elements
- Hover states for clickable items
- Loading indicators
- Transition animations (subtle)
- Drag and drop functionality where appropriate
- Tooltips for additional information

## Dark Mode Design
- Dark background colors (#121212, #1e1e1e)
- Light text colors (#f8f9fa, #e9ecef)
- Reduced brightness for visual comfort
- Maintained contrast ratios for accessibility
