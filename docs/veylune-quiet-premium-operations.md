# Veylune Quiet Premium Operations

This document is an internal operator workflow for preparing real content,
media, and governed Edition updates without expanding the public storefront.
It is operational documentation, not a new governance system.

## Boundary

This workflow may be used for internal preparation of product facts, media
sets, semantic drafts, and deployment checks.

It does not authorize:

- new public routes
- navigation or sitemap expansion
- route directories
- recommendation, relationship, archive, or collection behavior
- public product/CMS rendering inside governed Edition skeletons
- route-network or topology metadata exposure

## Operational Calmness

Operational calmness means that a repeatable operator can prepare content
without guessing where authority lives, which checks matter, or how to recover
from failure.

Governance ergonomics means the required checks protect the system without
forcing duplicated manual review.

Workflow sustainability means each content batch has a clear start, review
point, verification gate, and rollback target.

Premium operational discipline means media, facts, and copy are prepared with
the same restraint expected from the public surface.

Controlled realism means production-grade content can be tested internally
without creating public discovery, catalog, or route-topology pressure.

Operational fatigue is any repeated step that encourages bypassing governance,
copying stale metadata, or weakening the aggregate verification command.

## Daily Operator Workflow

Use one batch document per operational change. A batch may contain one governed
reference or several internal candidates, but only approved registered
references may become public.

1. Record the batch purpose and operator.
2. Prepare product facts before copy.
3. Prepare media assets before route registration changes.
4. Draft semantic fields only from approved vocabulary.
5. Review EN/DE parity before touching deployment metadata.
6. Confirm rollback target and semantic version metadata.
7. Run the aggregate governance check.
8. Stop on the first failure and correct the smallest cause.

The aggregate check is the deployment gate:

```bash
bin/veylune-governance-check
```

Do not run narrow checks as a substitute for the aggregate command before
deployment-sensitive changes.

## Content Intake Checklist

Record facts as scalar fields first:

- reference identifier
- locale coverage
- material facts
- dimensions, if applicable
- production or preparation status
- owner of the semantic draft
- owner of the review
- rollback target
- semantic version ID

Keep unapproved notes outside public payloads. Do not move workshop notes,
market positioning, sales intent, acquisition language, or relationship
language into governed scalar fields.

## Media Preparation Standards

Media should make objects legible without adding discovery or campaign energy.

Prepare image sets with stable ratios:

- primary object image: consistent portrait or near-square crop
- material/detail image: close crop with controlled texture visibility
- context image: only when it does not imply lifestyle recommendation,
  collection membership, or room-arrangement guidance

Use calm production constraints:

- consistent lighting direction within a batch
- neutral background discipline
- no heavy vignettes, dramatic color casts, or decorative overlays
- no text embedded in imagery
- no route labels, sequence marks, or collection indicators in filenames
- no public metadata that exposes batch count, route count, or topology state

Export assets with predictable behavior:

- sRGB color profile
- long edge prepared large enough for responsive crops
- compressed JPEG or WebP for photographic media
- PNG only when transparency is required
- descriptive internal filenames that identify the reference and view, not a
  public route network

## Product Preparation Sustainability

Prepare one complete reference before preparing many partial references.
Partial content increases fatigue because reviewers must reconstruct missing
context.

Recommended order:

1. Facts complete.
2. Media set complete.
3. EN scalar copy drafted.
4. DE scalar copy drafted.
5. Semantic parity reviewed.
6. Rollback metadata assigned.
7. Registry update prepared.
8. Aggregate governance check run once for the batch.

Avoid repetitive manual edits by using a batch checklist and reviewing the
diff before running governance checks. Re-running the aggregate command after
every field edit is unnecessary; run it once after a coherent batch is ready,
and again after any correction.

## Controlled Real-Content Simulation

Realistic content may be simulated internally by preparing a batch checklist
and media manifest without adding public routes or sitemap entries.

Example internal-only manifest shape:

| Field | Example | Public effect |
| --- | --- | --- |
| batch_id | `ops-batch-quiet-media-001` | none |
| candidate_reference | `material-study-internal-sample-01` | none |
| locales | `en-GB`, `de-DE` | none |
| media_slots | `primary`, `detail`, `context` | none |
| semantic_status | `draft` | none |
| route_registered | `false` | none |
| sitemap_allowed | `false` | none |

The simulation is valid only while it remains outside route registration,
navigation, sitemap generation, and public templates.

## Governance Review Ergonomics

Reviewers should answer four concrete questions:

1. Does the content remain scalar and governed?
2. Does the language avoid commercial, recommendation, relationship,
   discoverability, editorial, and prestige escalation?
3. Do EN and DE carry the same restraint?
4. Is rollback metadata present and understandable?

If the answer to any question is no, reject the batch before deployment
verification. Do not compensate for unclear content by adding new governance
machinery.

## Media Containment Rules

Media improvements must not imply:

- other available references
- related objects
- collection membership
- browsing sequence
- catalog scale
- recommendation behavior
- route adjacency
- public route topology

Visual consistency is allowed. Visual grouping that implies a public system of
references is not.

## Failed Batch Recovery

If a batch fails verification:

1. Keep the failed batch internal.
2. Identify the first failed gate.
3. Remove the smallest violating copy, media metadata, registry field, or
   template change.
4. Re-run the aggregate governance check.
5. Do not deploy partial fixes that leave semantic or topology uncertainty.

If a public route becomes denied because of content preparation, preserve
fail-closed behavior first and restore content continuity second.

## Operator Anti-Patterns

Do not:

- duplicate route records to speed up preparation
- copy approved semantic metadata without reviewing locale parity
- add homepage, navigation, or sitemap links for convenience
- introduce preview tokens or debug unlock behavior
- encode route sequence in filenames, labels, or copy
- treat real media readiness as permission for public discoverability
- weaken audit rules to reduce operational friction

## Sustainability Verdict

Quiet premium operations are sustainable when the operator can prepare real
content through a small, repeatable checklist; reviewers can audit the batch
without reconstructing intent; and deployment remains blocked by one aggregate
verification command.
