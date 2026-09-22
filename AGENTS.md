## 🚨 GLOBAL UI/UX RULE

This application uses Soft UI Dashboard as its established visual design system.

This rule applies to EVERY module and EVERY future feature.

Any AI coding agent MUST:

1. Inspect existing UI before creating a new interface.
2. Reuse existing Soft UI patterns.
3. Match existing cards, buttons, tables, forms, modals, badges,
   alerts, navigation, spacing, typography, shadows and responsive behavior.
4. Prefer existing components over creating new ones.
5. Extend an existing pattern when functionality is missing.

NEVER introduce a competing UI framework or visual language.

Do not introduce:
- Bootstrap
- Material UI
- shadcn
- arbitrary component libraries
- unrelated Tailwind design patterns

unless explicitly authorized.

A new screen must look like it was designed as part of the
existing application.