# Phase 2.3: Supplied Style Token Alignment

## Source analysis

The supplied 322-line computed-style snapshot exposes the active design-system token set: neutral, purple, status, spacing, motion, z-index, focus, shadow, radius, breakpoint, and type scales.

## Applied semantic mapping

- Ink `#211E22`; muted ink `#4D4A4F`
- Border `#D1D1D6`; surface `#F5F5F5`; page `#FFFFFF`
- Primary `#7B189F`; hover `#5C1277`; soft `#F8F3FA`
- Sale `#C4113F`; success `#247139`; rating `#F6B71D`
- Focus `#1364F1` with white inner and blue outer ring
- Spacing `2/4/8/12/16/24`; radius `2/4/8`
- Shadow progression based on 20% `#211E22`
- Type scale `13/16/20/25/31/39/49/61` pixels with 1.5 base line-height

## Font boundary

The computed source names `Sofia` and a proprietary fallback. No licensed font files were supplied, so Veylune retains its licensed Inter-led sans stack. A licensed Sofia-compatible asset can be integrated later without changing component contracts.
