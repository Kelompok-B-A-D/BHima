# Design System: Liquid Glass (Anti-Slop Edition)

## 1. Vibe

Calm, luminous, precise, and premium. Like light passing through clean water: soft, quiet, expensive. Not flashy, not techy-neon, not generic SaaS.

Material: Apple's Liquid Glass. Translucent, blurred, softly refractive glass floating over rich, edge-to-edge content.

**Two layers**
- Content layer: photography or a specific colorful scene, edge-to-edge, always the hero.
- Glass layer: navbar, sidebar, buttons, tabs, floating panels. Clear and mostly neutral so the content shows through.

---

## 2. Anti-Slop Rules (apply to every screen)

**Never do**
- No default purple-to-blue gradient hero. No random neon glows.
- No lorem ipsum, no "Acme", no "Your Company". Write real, specific copy for the product.
- No generic marketing phrases: "Unlock the power of", "Supercharge your workflow", "Seamless experience", "Next-level", "All-in-one solution".
- No emoji as icons. No clip-art style icons.
- No three identical feature cards in a row. Vary card sizes and content.
- No fake round stats (10,000+ users, 99.9%). Use specific, believable numbers (e.g. 2,418 orders this week, 41 ms median response).
- No stock-photo handshakes, laptops on desks, or floating 3D blobs with no meaning.
- No glass on top of glass. No glass on every element.
- No centered-everything layout. Mix left-aligned text, asymmetry, and varied section rhythm.

**Always do**
- Every image and illustration is described specifically and matches the product's story.
- Copy is short, concrete, and written in the product's own voice.
- Glass is used only for navigation and controls.
- One accent color, used only for the primary action.
- Keep one focal point per section.

---

## 3. Colors

Mood-based palette: deep ocean at dusk with a single warm highlight.

| Role | Light mode | Dark mode |
|---|---|---|
| Background base | #E9EEF5 | #060A14 |
| Scene color 1 (deep teal) | #0F766E | #0B4F4A |
| Scene color 2 (ink blue) | #1E3A8A | #172554 |
| Scene color 3 (warm sand) | #F2D8B3 | #7A5C3A |
| Highlight (coral, tiny use) | #FF7A59 | #FF7A59 |
| Primary action | #0A84FF | #3B9BFF |
| Text primary | #0B1020 | #FFFFFF |
| Text secondary | rgba(11,16,32,0.65) | rgba(255,255,255,0.70) |

Glass fills
- Regular: rgba(255,255,255,0.45) light, rgba(255,255,255,0.08) dark
- Clear: rgba(255,255,255,0.20) light, rgba(255,255,255,0.04) dark
- Strong (text-heavy panels): rgba(255,255,255,0.68) light, rgba(16,22,40,0.62) dark
- Edge: 1px gradient, bright top-left (white 90% light, 35% dark), fading to 10% bottom-right

Rule: only the primary action is tinted. Everything else is clear glass.

---

## 4. Typography

Font: Manrope (fallback: SF Pro, system-ui). Soft, elegant, slightly geometric.

| Style | Size | Weight | Tracking |
|---|---|---|---|
| Display | 72px | 700 | -0.03em |
| H1 | 56px | 700 | -0.025em |
| H2 | 40px | 600 | -0.02em |
| H3 | 28px | 600 | -0.015em |
| Body | 16px | 400 | 0 |
| Caption | 13px | 500 | 0 |

Large, tight headlines. Body text always high contrast. No thin light text directly on clear glass.

---

## 5. Shape and Borders

- Navbar, buttons, tabs, chips: fully rounded (pill).
- Cards and panels: 28px radius. Nested elements: 16px.
- Input fields: 16px radius, 1px translucent white border, soft inset shadow.
- Borders are thin and luminous (1px). No heavy outlines.
- Shadows are soft and layered, never flat or harsh.

---

## 6. Glass Recipe

1. Backdrop blur 24 to 40px, saturation 160%.
2. Translucent fill (see Colors).
3. 1px gradient edge highlight.
4. Faint inner glow on the top edge.
5. Layered shadow: 0 2px 6px rgba(0,0,0,0.06), 0 12px 32px rgba(20,30,80,0.12).

Variants: Regular (default), Clear (over vivid imagery, large bold text only), Tinted (primary button only).
Interaction: soft light sheen follows the cursor. Pressed state scales to 0.97.

---

## 7. Imagery

Describe imagery specifically for each project. Defaults for this system:
- Hero background: macro photograph of rippling ocean water at golden hour, deep teal and ink blue with warm sand highlights, softly out of focus at the edges.
- Product or UI previews: real-looking screens with believable data, placed inside a glass frame.
- Illustrations: none unless requested. Prefer photography and real UI over decorative graphics.
- When the theme color changes, ensure all images and icons match the new color scheme.

---

## 8. Components

- **Navigation bar:** floating pill, regular glass, centered, 16px from top. Logo, 4 links, search icon, one tinted call-to-action button.
- **Primary button:** pill, tinted #0A84FF, white label, 48px tall.
- **Secondary button:** pill, clear glass, 1px edge, same size.
- **Card layout (bento):** varied sizes (1x1, 2x1, 1x2), 28px radius, regular glass, specific content in each, never identical.
- **Segmented control:** clear glass track with a raised glass pill that slides between options.
- **Sidebar (dashboard):** floating left glass panel, 280px, content scrolls underneath.
- **Stats bar:** one wide pill glass bar, 3 specific numbers, thin dividers.
- **Testimonial card:** regular glass, real-sounding quote with name, role, and company.
- **Modal:** strong glass, 32px radius, dimmed blurred backdrop.
- **Icons:** simple line icons, 1.5px stroke, rounded caps, consistent set.

---

## 9. Layout

- Content is edge-to-edge and passes under the navigation bar.
- 12-column grid, 1200px max width, generous whitespace.
- Asymmetric hero: left-aligned text, large glass preview on the right.
- Mobile: floating pill bottom tab bar, single column, 16px gutters.

---

## 10. Motion and Accessibility

- Springy, fluid transitions, 250 to 450ms. Subtle scroll reveals (fade, slight scale, blur-in).
- Text contrast WCAG AA. Add a strong fill behind text on glass when needed.
- Opaque fallback when transparency is reduced. Touch targets 44px minimum. Visible focus rings.
