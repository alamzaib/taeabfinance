# Logo Setup Instructions

## Logo Image Placement

To use the logo in the frontend application, please follow these steps:

1. **Copy the logo image** from the admin section:
   - Source: `backoffice/public/vendor/adminlte/dist/img/AdminLTELogo.png`
   - Or use the logo image you provided

2. **Place the logo** in the frontend public folder:
   - Destination: `frontend/public/images/logo.png`
   - The logo should be named `logo.png` (or update the Logo component if using a different name)

3. **Supported formats**: PNG, SVG, or JPG

## Logo Component

The logo is used via the `Logo` component located at:
- `frontend/components/Logo.tsx`

If you need to change the logo path or styling, edit this component.

## Current Logo Path

The Logo component expects the logo at: `/images/logo.png`

Make sure the image file exists at `frontend/public/images/logo.png`

