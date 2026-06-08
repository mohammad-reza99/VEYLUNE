# VEYLUNE STUDIO - WP-DES-03

## Wave 1 Luxury Commerce Recovery Design Specification

**Specification date:** 7 June 2026  
**Phase:** Design recovery  
**Authorization:** Design specification only  
**Baseline:** WP-DES-01 score 28/100  
**Wave 1 target:** 42/100

This document defines the exact visible target for Wave 1. It authorizes no
implementation, code change, template change, stylesheet change, script change,
route change, configuration change, or catalog mutation.

## Governing Design Rule

```text
A visitor sees a complete public destination or no destination link.
They never see a failed route, internal governance state, empty shell,
registry language, approval language, or future-population promise.
```

Wave 1 is a credibility recovery, not a full redesign. It preserves:

- the approved department, room, and collection structure;
- the approved Commerce UX Architecture Phase 1 hierarchy;
- the Veylune color, material, and editorial identity;
- Wave 2 ownership of product-detail continuity, assortment breadth, and
  commerce-state logic;
- Wave 3 ownership of full homepage pacing and spacing recovery.

This specification defines supporting component states only where necessary to
make the five approved Wave 1 priorities visibly complete.

---

# PART I - Wave 1 Scope

## Approved Issues

| Order | WP-DES-01 Issue | Current State | Desired State | Reason for Priority |
| ---: | ---: | --- | --- | --- |
| 1 | 1 | "Page not found" occupies the global header on normal storefront routes. | Every normal public screen begins with a clean, complete commerce header. Error content appears only on a dedicated error page. | The current state makes the entire platform look broken. |
| 2 | 10 | Homepage and destinations expose governance, registry, approval, shell, and future-population language. | All visible copy addresses customer value, selection logic, material context, or service. | Internal language makes a public platform look unfinished. |
| 3 | 6 | Multiple primary departments lead to visible empty-product states. | Only commercially credible department destinations are exposed as active links; no active department advertises emptiness. | Empty departments undermine category and supplier credibility. |
| 4 | 7 | Four of six room destinations are visible empty shells. | Only credible room destinations are active; each visible room entry presents a complete room proposition. | Empty rooms damage discovery quality across the storefront. |
| 5 | 8 | Permanent and Editorial Collections visibly present empty states. | Only populated, authored collections are active; inactive collection concepts remain invisible to visitors. | Empty collections invalidate the promise of curation. |

## Explicit Wave 1 Boundaries

Wave 1 does **not**:

- invent products to fill grids;
- duplicate products to simulate assortment;
- activate an incomplete product-detail journey;
- resolve inquiry versus cart logic;
- complete product-card commerce hierarchy;
- add filters, sorting, or new discovery structures;
- change the approved taxonomy;
- redesign the identity system;
- attempt the full Wave 3 homepage-spacing recovery.

## Completion Test

Wave 1 is visually complete only when:

1. no normal storefront route displays error content;
2. no customer-facing text describes internal operating state;
3. no active category link ends in an empty category shell;
4. no active room link ends in an empty room shell;
5. no active collection link ends in an empty collection shell;
6. header and menu states remain visually coherent at desktop and mobile sizes;
7. suppressed destinations leave no visible blank cards, empty columns, or
   placeholder gaps.

---

# PART II - Header Recovery Specification

## Target Experience

The header must feel quiet, operational, and immediately understandable. It
must establish Veylune, expose the primary discovery paths, and make commerce
utilities visible without competing with the page.

## Desktop Hierarchy

```text
Optional service strip
---------------------------------------------------------------
Logo | Shop | Rooms | Collections | Journal | Consultation
                                      Search | Account | Saved | Bag
---------------------------------------------------------------
Page content
```

### Header Levels

| Level | Content | Visual Role |
| --- | --- | --- |
| Service strip | Delivery proposition, consultation, locale | Secondary reassurance |
| Main header | Logo, five primary navigation items, four utility actions | Primary orientation |
| Active state | Current navigation context | Quiet location signal |

No title, campaign copy, system message, empty state, or error message may
appear inside the global header.

## Logo Positioning

- Desktop logo sits at the far left of the main header.
- It is vertically centered with the navigation and utilities.
- It is the first visual anchor, not a pale floating mark between unrelated
  columns.
- The logo uses one expression only; duplicate "VEYLUNE STUDIO" wordmarks are
  not shown in the same header.
- Clear space around the logo equals at least its cap height on all sides.
- Target desktop wordmark width: 150-180 px.
- Target mobile wordmark width: 112-132 px.

## Navigation Hierarchy

Primary navigation remains exactly:

1. Shop
2. Rooms
3. Collections
4. Journal
5. Consultation

Rules:

- `Shop` is visually first and carries the strongest discovery emphasis.
- Navigation labels use one size, one baseline, and one spacing rhythm.
- Active or open items use a restrained underline or color change, not a box.
- No top-level item appears if its immediate destination is an empty public
  shell.
- Suppression changes visibility, not the approved taxonomy.

## Utility Actions

Desktop order:

1. Search
2. Account
3. Saved
4. Bag

Visible-state rules:

- Search and Bag are always recognizable without interpretation.
- Bag includes its item count, including `0`.
- Account and Saved may use icons, but all icons share one stroke weight and
  optical size.
- Utility actions form one aligned group.
- Locale controls belong in the service strip or a low-priority utility area;
  they do not float above the header.
- No utility control uses a heavier visual treatment than the primary
  navigation.

## Search Entry

- Desktop presents an icon plus the word `Search`.
- Search occupies no more visual weight than one navigation item.
- It remains visible in the sticky state.
- Mobile presents a clearly sized search icon in the top action row.
- Search never shares space with error messaging or page titles.

## Cart Visibility

- The commerce architecture term is `Bag`; the visible label remains
  consistent across desktop and mobile.
- Desktop presents `Bag (0)` or the appropriate current count.
- Mobile presents a bag icon with a persistent count indicator.
- Bag visibility is equal to Search visibility.
- A visitor must not need to open a menu to confirm that bag functionality
  exists.

## Dimensions and Spacing

| Element | Desktop Target | Mobile Target |
| --- | ---: | ---: |
| Service strip height | 28-32 px | 0-28 px; omit when no useful message exists |
| Main header height | 72-80 px | 60-68 px |
| Horizontal page inset | 32-48 px | 16-20 px |
| Logo-to-navigation gap | 40-56 px | Not applicable |
| Navigation item gap | 24-32 px | Menu list: 20-24 px vertical |
| Utility item gap | 18-24 px | 16-20 px |
| Minimum action target | 40 x 40 px | 44 x 44 px |

## Alignment

- All main-header elements share one vertical centerline.
- Navigation and utility groups sit on the same baseline.
- The logo is not vertically offset by a subtitle.
- The header container aligns with the primary page container.
- No element occupies a separate third column merely because space exists.

## Sticky Behavior

- The main header becomes sticky after the service strip leaves the viewport.
- Sticky height is 64-72 px desktop and 56-64 px mobile.
- The sticky state retains logo, primary navigation trigger, Search, and Bag.
- Account and Saved may remain on desktop; mobile places them inside navigation.
- The sticky state uses an opaque warm-white surface with a subtle bottom
  boundary.
- It does not blur page content into illegibility.
- Mega-menu opening locks the header in its stable, fully visible state.

## Mobile Header

```text
Menu | Veylune wordmark | Search | Bag
```

- One horizontal row only.
- No language selector, currency selector, error text, or page title competes
  in this row.
- The logo remains centered optically, not mathematically displaced by unequal
  side controls.
- Menu, Search, and Bag use equal 44 px action areas.
- The first page heading begins below a consistent header boundary.

## Westwing and Apple Translation

- **Westwing:** obvious shopping entry, visible search, visible bag, broad
  discovery access.
- **Apple:** one controlled row, consistent alignment, low-noise utilities,
  predictable sticky behavior.
- **Veylune:** warm-white surface, restrained typography, quiet active states,
  and no promotional clutter.

---

# PART III - Mega Menu Recovery Specification

## Target Experience

The mega menu must feel like a controlled discovery layer above the page. It
must never look trapped beneath another component, overlap failure content, or
expose empty destinations.

## Layering

- The menu begins directly below the active header.
- It sits above all page content and media.
- No page title, hero, error panel, cookie element, or other content can appear
  between the header and menu.
- A uniform page scrim reduces background competition.
- The menu surface is fully opaque.
- The menu and scrim occupy one unambiguous top layer beneath the header.

## Width and Container

- Menu surface spans the full viewport width.
- Menu content uses the same maximum container as the page: 1280-1360 px.
- Desktop horizontal inset: 40-48 px.
- Content does not float in a narrow centered card.
- Maximum menu depth should remain within 70-75% of the visible viewport height;
  overflow becomes an internal menu scroll only when unavoidable.

## Shop Menu Structure

Four visual columns:

| Column | Content |
| --- | --- |
| 1 | Furniture |
| 2 | Lighting; Decor & Objects |
| 3 | Textiles & Rugs; Dining & Kitchen; Outdoor |
| 4 | Featured: New Arrivals, Founder Selection, eligible current collection, View All |

Rules:

- Department names are group headings.
- Product types are subordinate links.
- Empty or sparse branches remain hidden, as required by the approved Commerce
  UX Architecture.
- A hidden branch leaves no blank heading or reserved gap.
- Featured links appear only when their destinations meet the same complete
  public-state rule.

## Rooms Menu Structure

Three visual columns:

| Column | Content |
| --- | --- |
| 1 | Living Room; Dining Room |
| 2 | Bedroom; Workspace |
| 3 | Hallway; Outdoor |

- Only currently credible rooms appear as active links.
- Hidden rooms do not leave blank columns; remaining rooms rebalance across the
  available width.
- Each room may show up to three approved category shortcuts only when those
  shortcuts lead to complete destinations.

## Collections Menu Structure

Three visual columns:

| Column | Content |
| --- | --- |
| 1 | Founder Selection |
| 2 | New Arrivals |
| 3 | Eligible Permanent or Editorial Collections |

- Permanent and Editorial labels do not appear merely to advertise future
  structure.
- No empty collection card, disabled link, "coming soon," or approval message
  is shown.
- If only Founder Selection and New Arrivals qualify, the menu uses a balanced
  two-column composition.

## Typography

| Role | Target |
| --- | --- |
| Menu eyebrow | 11-12 px uppercase, moderate tracking |
| Group heading | 20-24 px serif or 15-16 px medium sans; one treatment only |
| Link | 15-17 px, regular |
| Supporting line | 13-14 px, muted |

- Link typography is more legible than the current small navigation labels.
- The display serif is reserved for group emphasis, not every link.
- All links use consistent line height and left alignment.

## Spacing

| Relationship | Target |
| --- | ---: |
| Menu top/bottom padding | 36-48 px |
| Column gap | 40-64 px |
| Heading-to-first-link | 16-20 px |
| Link-to-link | 10-14 px |
| Group-to-group | 28-36 px |

## Hover and Focus Behavior

- Hover changes text color and adds a restrained underline or directional cue.
- No link shifts position on hover.
- Active top-level navigation remains visibly active while the panel is open.
- Keyboard focus is at least as visible as hover.
- Moving from navigation trigger into the menu does not close the panel.
- Moving between top-level triggers replaces content without page flicker.
- Closing returns focus to the initiating trigger.

## Experience Standard

The menu should communicate:

```text
breadth without clutter
hierarchy without administration
curation without hiding the commerce structure
```

---

# PART IV - Discovery Visibility Recovery

## Approved Priority

```text
1. Categories
2. Rooms
3. Collections
4. Materials
5. Style
```

Wave 1 focuses on the first three. It does not create parallel discovery
systems.

## Attention Hierarchy

| Discovery Mode | Visual Priority | Role |
| --- | --- | --- |
| Categories | Dominant | Stable commercial backbone |
| Rooms | Secondary | Inspirational and contextual entry |
| Collections | Tertiary but authored | Curated reason products belong together |

Categories should receive approximately half of the initial discovery emphasis;
Rooms approximately one third; Collections the remaining share. This is a
visual hierarchy, not a taxonomy change.

## Category Visibility Specification

- Category access appears before room and collection access on the homepage.
- The six approved departments remain the long-term structure.
- During Wave 1, only departments with credible public outcomes are active.
- Inactive departments are not shown as empty cards, disabled cards, or future
  promises.
- A visible category card contains:
  1. department name;
  2. one concise customer-facing descriptor;
  3. one relevant image or a deliberately typographic treatment;
  4. one clear destination action.
- Terms such as `foundation`, `registry`, `governed`, `approval`, `prepared`,
  and `future attribution` do not appear.
- Category destination opening content contains:
  1. breadcrumb;
  2. category title;
  3. one-sentence customer purpose;
  4. visible product count when reliable;
  5. valid subcategory shortcuts;
  6. product content without an empty-state announcement.

## Room Visibility Specification

- Rooms follow the first commerce-product module, not the primary category
  access.
- Visible room cards use room-specific imagery or a complete room proposition.
- A room card is not exposed merely because the room exists in taxonomy.
- A credible visible room destination contains:
  1. room title;
  2. short functional definition;
  3. valid category anchors;
  4. at least one coherent product group;
  5. optional consultation support where justified.
- Room destinations do not share identical generic imagery or identical
  placeholder compositions.
- Inactive rooms remain absent from public navigation and homepage modules
  until their visible outcome is complete.

## Collection Visibility Specification

- Founder Selection and New Arrivals remain distinct.
- A visible collection must have:
  1. an explicit customer-facing selection rationale;
  2. a primary visual or deliberate product-led opening;
  3. direct access to its governed products;
  4. no empty state or future-population text.
- Permanent and Editorial Collections are not visible as generic parent
  destinations when they contain no credible current child or product set.
- Editorial Collections require a visible editorial premise; Permanent
  Collections require a stable assortment premise.
- A collection is not represented by an empty bordered panel.

## Empty-State Policy

For Wave 1 public discovery:

| Condition | Visible Result |
| --- | --- |
| Complete and credible | Active destination |
| Temporarily insufficient | Destination and link suppressed |
| Removed after prior publication | Purposeful dedicated unavailable state outside normal discovery |
| Invalid route | Dedicated error page, never global-header content |

No public discovery screen uses "awaiting population" as its primary content.

---

# PART V - Product Visibility Recovery

## Scope Boundary

Wave 1 does not resolve WP-DES-01 issues 4, 5, 12, or 31 in full. Those remain
Wave 2. Wave 1 nevertheless requires a fixed visual target so populated
category, room, and collection destinations do not introduce new design
decisions later.

## Why Products Currently Feel Weak

From WP-DES-01:

- only two products repeat across several surfaces;
- desktop grids leave most width unused;
- imagery has inconsistent style;
- image cards contain unfinished muted fields;
- price, availability, shipping, and action emphasis compete;
- product discovery does not lead to a dependable visible evaluation journey.

Wave 1 must not disguise these limitations by duplicating cards or stretching
two cards across an empty four-column grid.

## Product Card Hierarchy

```text
1. Product image
2. Optional bounded badge
3. Product type / supplier when meaningful
4. Product title
5. Material or variant summary
6. Price
7. Delivery or availability signal
8. One primary destination action
9. Saved action
```

## Image Dominance

- Image occupies 65-72% of the card's perceived height before actions.
- Standard listing ratio: 4:5 portrait.
- Editorial two-up modules may use 3:4.
- Images fill their reserved frame; no unexplained muted lower field appears.
- Product scale and crop remain consistent within one row.
- Text never overlays the primary product image except a small approved badge.
- Image background style is consistent within one module.

## Product Title

- Product title is the strongest text after the image.
- Target desktop size: 20-24 px.
- Target mobile size: 17-20 px.
- Maximum visible length: three lines desktop, two to three lines mobile.
- Titles use the approved serif only when legibility is preserved.
- Product type and supplier remain subordinate.

## Price Treatment

- Price appears on one clear line.
- It is visually stronger than shipping disclosure and availability.
- Target size: 16-18 px desktop; 15-17 px mobile.
- Tax and shipping information is supporting text, not a bordered competing
  component.
- No price treatment resembles a warning or administrative notice.

## Card Spacing

| Relationship | Target |
| --- | ---: |
| Image to metadata | 16-20 px |
| Metadata to title | 6-8 px |
| Title to summary | 8-10 px |
| Summary to price | 14-18 px |
| Price to availability | 6-8 px |
| Availability to action | 16-20 px |
| Card-to-card desktop | 24-32 px |
| Card-to-card mobile | 12-16 px |

## Grid Rhythm

- Desktop category grid target: four columns at wide desktop, three at medium
  desktop.
- Tablet target: two or three columns.
- Mobile target: two columns for standard cards.
- All cards in a row align at image top and primary-action baseline where
  actions are present.
- Sparse approved inventory uses a bounded two-up editorial module aligned to
  the main container.
- Sparse inventory is not spread across a nominal four-column grid with two
  empty tracks.
- The same product does not repeat within one page to manufacture density.

## Desired Feeling

Products should feel:

- visually primary;
- comparable without becoming marketplace-like;
- factual enough to inspire confidence;
- quiet but obviously purchasable or explorable;
- consistently represented across category, room, and collection contexts.

---

# PART VI - Homepage Rhythm Recovery

## Wave 1 Homepage Order

This order follows Commerce UX Architecture Phase 1 and suppresses any module
that cannot produce a complete visible state.

```text
1. Service strip, when useful
2. Global header
3. One primary hero
4. Shop by Category
5. New Arrivals, only when distinct and credible
6. Shop by Room, eligible rooms only
7. Founder Selection, only when distinct from New Arrivals
8. Material Focus, only when destination depth is credible
9. One eligible commercial or editorial collection
10. Consultation
11. Trust architecture
12. Footer
```

Best Sellers or Most Considered remain omitted unless their approved evidence
threshold exists.

## Section Order Rules

- Category access is the first discovery module after the hero.
- Product proof appears before extended editorial content.
- New Arrivals and Founder Selection never appear with identical product sets.
- Empty room and collection modules are omitted entirely.
- Consultation remains supportive and late in the journey.
- Trust content precedes the footer.
- One section performs one job.

## Visual Pacing

Homepage pacing follows a repeating three-beat rhythm:

```text
orientation -> product/discovery proof -> atmosphere
```

No two full-width text-only statements appear consecutively.

No section is taller than one desktop viewport unless it contains a dominant
image with a complete proposition.

No blank transition exceeds 160 px desktop or 96 px mobile.

## Content Density

| Section Type | Target Density |
| --- | --- |
| Hero | One proposition, two actions maximum, one dominant visual |
| Category access | 3 cards per row desktop; complete six-card system only when all are credible |
| Product module | 4-8 distinct products; use deliberate two-up module when only two qualify |
| Room access | 3 cards per row desktop; eligible rooms only |
| Collection feature | One authored collection with direct product access |
| Consultation | One proposition, one primary action, up to three service reasons |
| Trust | 3-5 concise proof points |

## Transition Quality

- Adjacent sections differ through content, image scale, or subtle surface
  change, not arbitrary empty height.
- Section boundaries align to the same container grid.
- Headings remain visually attached to their content.
- The next section should become partially visible near the bottom of a typical
  desktop viewport, creating forward momentum.
- Large images may bleed to the viewport edge; text remains container-aligned.
- No transition exposes internal production terminology.

## Wave 1 Versus Wave 3

Wave 1 removes empty modules and the dead space directly created by their
absence. Wave 3 remains responsible for comprehensive homepage pacing,
oversized editorial statements, and final spacing refinement.

---

# PART VII - Typography Recovery

## Type Roles

| Role | Desktop | Mobile | Use |
| --- | ---: | ---: | --- |
| Display hero | 64-88 px | 42-56 px | One primary proposition |
| Page title | 52-72 px | 36-48 px | Category, room, collection title |
| Section title | 38-52 px | 30-38 px | Major module heading |
| Card title | 20-24 px | 17-20 px | Product or discovery card |
| Body lead | 18-22 px | 17-19 px | Short proposition |
| Body | 15-17 px | 15-17 px | Explanatory content |
| Utility/navigation | 13-15 px | 15-17 px in menus | Controls and navigation |
| Eyebrow/metadata | 11-12 px | 11-12 px | Sparse contextual labels |

## Hierarchy Rules

- One display-scale heading per viewport.
- A page title is never visually weaker than an error, utility, or empty-state
  message.
- Section headings do not exceed the page title.
- Product names outrank metadata, availability, and shipping text.
- Eyebrows never carry essential meaning alone.
- Body copy uses sentence case.
- Uppercase is limited to short labels.

## Luxury Perception

Luxury is expressed through:

- controlled scale;
- clear contrast between display and functional text;
- short line lengths;
- consistent alignment;
- deliberate restraint in uppercase tracking;
- adequate white space around meaningful content.

Luxury is not expressed through:

- making every heading monumental;
- faint text;
- excessive tracking at small sizes;
- long internal explanations;
- using the serif for every interface role.

## Consistency Rules

- Maximum two typeface families on one screen.
- Serif owns brand, page, section, and selected card titles.
- Sans serif owns navigation, utilities, controls, price support, and system
  feedback.
- Recommended body line length: 50-70 characters.
- Display line length: 8-14 words where possible.
- No orphaned one-word final line in primary headings when avoidable.
- The same semantic role uses the same size and weight across category, room,
  and collection pages.

---

# PART VIII - Spacing System Recovery

## Base Scale

All visible spacing decisions use this approved scale:

```text
4, 8, 12, 16, 24, 32, 48, 64, 80, 96, 128
```

Intermediate values are reserved for optical correction, not routine layout.

## Vertical Spacing

| Context | Desktop | Mobile |
| --- | ---: | ---: |
| Header to page content | 32-48 px | 24-32 px |
| Page-title block top/bottom | 64-96 px | 40-64 px |
| Major section top/bottom | 80-128 px | 56-80 px |
| Compact product section | 64-96 px | 48-64 px |
| Heading to lead | 16-24 px | 12-20 px |
| Heading block to content | 32-48 px | 24-32 px |
| Paragraph to action | 24-32 px | 20-24 px |
| Final section to footer | 80-96 px | 56-72 px |

No content-free spacer exceeds 160 px desktop or 96 px mobile.

## Horizontal Spacing

| Viewport | Page Inset | Column Gap |
| --- | ---: | ---: |
| 1440 px and above | 40-48 px | 24-32 px |
| 1024-1439 px | 32-40 px | 20-28 px |
| 768-1023 px | 24-32 px | 16-24 px |
| Below 768 px | 16-20 px | 12-16 px |

## Container Behavior

- Primary maximum content width: 1280-1360 px.
- Reading-content maximum width: 680-760 px.
- Product and discovery grids use the primary container.
- Full-bleed imagery may extend beyond the container; related text remains
  aligned to a container edge.
- Header, hero text, section headings, product grids, and footer columns share
  common left and right anchors.
- Sparse content uses a deliberately narrower composition, not accidental
  empty grid tracks.

## Grid Consistency

- Use a 12-column desktop grid.
- Category and room cards use three or six equal tracks according to content.
- Product grids use four tracks at wide desktop and three at medium desktop.
- Collection features may use an 8/4 or 7/5 editorial split.
- Mobile uses a four-column structural grid; standard product cards occupy two
  columns each.
- Grid gaps remain consistent inside one module.

## Alignment Principles

1. Align related headings, content, and actions to one edge.
2. Do not center text by default; reserve centering for bounded statements.
3. Align product-card image tops and information blocks.
4. Keep controls on stable baselines.
5. Use asymmetry only when the visual weight is balanced by image or content.
6. Never use blank space to compensate for missing content.
7. Suppressed modules collapse completely without leaving reserved height.

---

# PART IX - Westwing / Apple Translation

## Area-by-Area Translation

| Wave 1 Area | Westwing Influence | Apple Influence | Veylune Interpretation |
| --- | --- | --- | --- |
| Header | Clear Shop entry, search, account, saved, bag | One controlled global bar and stable action hierarchy | Warm, quiet, low-promotion expression |
| Mega menu | Department breadth and commerce depth | Precise grouping, clean layering, predictable interaction | Curated visibility; empty branches remain absent |
| Categories | Commercial backbone and direct product access | Concise title, clear purpose, immediate next action | Material-aware language and restrained imagery |
| Rooms | Inspirational discovery with product pathways | Focused destination proposition | Spatial relevance and consultation where justified |
| Collections | Merchandising and cross-category grouping | One clear rationale and direct action | Founder authorship and material discipline |
| Product visibility | Image-led browsing and comparable cards | Strong information order and controlled action hierarchy | Quiet object presentation without marketplace noise |
| Homepage | Commercial sequence and assortment visibility | Modular clarity and deliberate pacing | Editorial atmosphere used selectively |
| Typography | Readable commerce labels and product density | Disciplined scale and concise copy | Distinctive serif voice in bounded roles |
| Spacing | Enough density to sustain shopping | Purposeful white space and alignment | Calm rhythm without visible emptiness |

## Ratio Interpretation

The final strategic target remains:

| Influence | Target |
| --- | ---: |
| Westwing | 40% |
| Apple | 40% |
| Veylune | 20% |

Wave 1 is expected to reach only:

| Influence | Wave 1 Forecast |
| --- | ---: |
| Westwing | 15% |
| Apple | 25% |
| Veylune | 60% |

Wave 1 cannot credibly reach 40/40/20 because assortment breadth, dependable
product journeys, commerce-state clarity, and deeper merchandising remain Wave
2 and later responsibilities.

## Translation Rules

- Westwing influence means useful commerce breadth, not promotional density.
- Apple influence means clarity and completeness, not visual imitation.
- Veylune influence means material and spatial authority, not oversized type
  or excessive emptiness.
- No component copies a competitor's visual identity.
- The ratio is measured through behavior and hierarchy, not superficial style.

---

# PART X - Final Wave 1 Design Package

## Recovery Package

| Priority | Recovery Target | Expected Impact | Complexity | Estimated Experience Gain |
| ---: | --- | --- | --- | ---: |
| 1 | Remove global visible error state | Restores basic platform and header credibility on every route | Small | +6 |
| 2 | Replace internal governance language with customer-facing content | Converts unfinished system messaging into a public luxury voice | Small | +3 |
| 3 | Prevent active empty department destinations | Restores category credibility and supplier confidence | Large | +2 |
| 4 | Prevent active empty room destinations | Stops discovery from leading to placeholder experiences | Large | +2 |
| 5 | Prevent active empty collection destinations | Protects curation authority | Medium | +1 |

**Projected Wave 1 gain: +14 points**

## Area Score Forecast

| Area | Current | Wave 1 Target | Gain |
| --- | ---: | ---: | ---: |
| Homepage | 33 | 45 | +12 |
| Header | 18 | 48 | +30 |
| Mega Menu | 24 | 39 | +15 |
| Categories | 27 | 42 | +15 |
| Rooms | 22 | 40 | +18 |
| Collections | 25 | 42 | +17 |
| Product Listings | 31 | 36 | +5 |
| Typography | 46 | 48 | +2 |
| Spacing | 25 | 31 | +6 |
| Luxury Feel | 34 | 44 | +10 |
| Supplier Impression | 26 | 46 | +20 |

The area gains are not additive. The overall score moves from 28 to 42 because
the same global corrections improve several screens simultaneously.

## Why 28/100 Becomes 42/100

### Global Error Removal: Largest Gain

The header currently makes every page look broken. Removing the visible failure
state improves first impression, navigation balance, mega-menu layering, mobile
composition, and supplier confidence at once.

### Public-Language Recovery: High Gain at Low Complexity

Removing registry, governance, approval, shell, and future-population language
stops the storefront from describing its unfinished operating state.

### Destination Visibility Control: Credibility Gain

Suppressing incomplete category, room, and collection destinations prevents
visitors from repeatedly discovering emptiness. It does not create false
assortment and does not weaken governance.

### Supporting Design Control: Moderate Gain

The header, menu, typography, grid, and spacing rules ensure that removing
failed and empty states does not leave malformed gaps or require new visual
decisions during implementation.

## Wave 1 Acceptance Checklist

### Global

- [ ] No normal public route displays "Page not found."
- [ ] No normal page reserves header space for error content.
- [ ] No visible copy uses internal governance or population language.

### Header

- [ ] One logo expression is used.
- [ ] Navigation and utilities share one aligned main row.
- [ ] Search and Bag are visible.
- [ ] Desktop and mobile headers contain no page or error content.
- [ ] Sticky behavior preserves orientation and commerce actions.

### Mega Menu

- [ ] Menu sits above the page without overlap.
- [ ] Background content is visually subordinate.
- [ ] Empty destinations and branches are absent.
- [ ] Suppressed items leave no blank columns or gaps.

### Discovery

- [ ] Every visible category leads to a complete public state.
- [ ] Every visible room leads to a complete public state.
- [ ] Every visible collection leads to a complete public state.
- [ ] Categories remain dominant over Rooms and Collections.

### Homepage

- [ ] Empty modules collapse completely.
- [ ] Category access appears before room and collection depth.
- [ ] No two empty or text-only full-viewport sections occur consecutively.
- [ ] Existing product proof is not duplicated to manufacture density.

### Typography and Spacing

- [ ] One display-scale heading appears per viewport.
- [ ] Headings remain attached to their content.
- [ ] No content-free spacer exceeds the defined limit.
- [ ] All primary content aligns to the shared container grid.

## Final Target Experience

After Wave 1, Veylune should no longer feel visibly broken or internally
unfinished.

It should feel like:

- a controlled public storefront;
- a selective platform that hides incomplete paths rather than advertising
  them;
- a commerce experience with clear global orientation;
- a Veylune identity supported by discipline rather than excess;
- a credible foundation ready for Wave 2 product and commerce proof.

Wave 1 does not make Veylune a complete luxury-commerce platform. It removes
the visible conditions that currently prevent visitors and suppliers from
believing that such a platform exists.

