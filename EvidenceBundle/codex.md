I want to design and eventually implement a software tool for documenting evidence and producing a formal evidence bundle.

The immediate goal is to get the **conceptual/domain model right before thinking too much about UI or implementation**.

## Core idea

The underlying data should be an **interlinked graph of typed items**, rather than a conventional document outline.

The graph should support two views of the same underlying information:

1. A React-based webpage showing the evidence as an interactive graph.
2. A conventional plain-text/formal evidence-bundle reading, generated from the graph.

The formal bundle should therefore be a **view/compiler output of the underlying evidence graph**, rather than the primary representation.

## Important conceptual distinction

I think the central structure should be something along the lines of:

`Source/Document → Evidence/Observation → Proposition → Inference → Allegation`

The system needs to preserve distinctions between:

* a source document
* a specific piece/location of evidence within that source
* a factual proposition that the evidence supports
* an inference drawn from propositions
* an allegation/conclusion
* a legal element that an allegation needs to establish
* a legal duty
* the conduct/event that may breach that duty
* the legal authority establishing the relevant law/duty/element

The tool should not collapse all of these into generic "evidence" objects.

## Candidate node types

The current proposed domain types are:

* `Person`
* `Office / Role`
* `Event`
* `Document`
* `Evidence`
* `Proposition`
* `Inference`
* `Allegation`
* `LegalElement`
* `Duty`
* `LegalAuthority`
* possibly `Conduct`

`Proposition` is particularly important. It represents a factual statement the user is trying to establish.

For example:

> P1 — Tim O'Gara was the qualified person who gave the FOIA s36 opinion on 24 June 2025.

An `Evidence` object would point to the actual location in a source document that supports P1.

An `Allegation` would be something like:

> X's conduct potentially constituted misconduct in public office.

A `LegalElement` would represent one of the elements that needs to be established for that allegation.

A `Duty` is distinct from a legal element: it represents something the person/office was required to do.

## Evidence/source detail

One minor but useful requirement that I want incorporated somewhere in the model:

A source/document should be able to have:

* the **plain-text reading of the source**, or `null` where that isn't applicable;
* a **screenshot/image of the source document**, to make checking the evidence easier.

For example, a PDF/email/webpage might have extracted text, while another source may only have an image/screenshot.

This is mainly provenance/checking functionality rather than a fundamental part of the conceptual legal model. I don't want it to obscure the more important distinction between `Document`, `Evidence`, and `Proposition`.

An `Evidence` item should be capable of identifying a precise location within the source, such as:

* page
* paragraph
* section
* line/range
* screenshot region
* quoted text

The user should ideally be able to click an Evidence node and immediately see the relevant source and location/highlight.

## Candidate relationships

The graph needs typed edges rather than arbitrary links.

Some candidate relationships are:

* `held`
* `participated_in`
* `occurred_before`
* `occurred_after`
* `supports`
* `contradicts`
* `refutes`
* `establishes`
* `alleges`
* `addresses`
* `breaches`
* `derived_from`
* `cites`
* `explains`

These are provisional. I want to think carefully about whether these should be the actual domain relationships, whether some should be properties of other relationships, and whether any important relationships are missing.

A possible basic edge representation is:

```ts
type Edge = {
    id: string
    from: string
    to: string
    type: EdgeType
    qualification?: string
}
```

But I want to avoid prematurely settling on this if a better model emerges.

## Provenance and reasoning

One of the most important requirements is that the system should be able to answer:

> "Why do you think this?"

For example, starting at an allegation, the user should be able to traverse something like:

`Allegation → LegalElement → Proposition → Evidence → Document → Page/paragraph/quote`

It should also be possible to see:

> "What evidence contradicts this?"

using explicit contradiction/refutation relationships.

The system should distinguish between:

* directly evidenced fact
* inference
* allegation
* uncertainty/unknown
* contradictory evidence

I don't want the application to accidentally present an inference as though it were a fact.

## Legal authorities

Legal authorities should ideally be structured rather than just free text.

For example:

```ts
type StatuteProvision = {
    type: "statute"
    title: string
    year: number
    section: string
    url?: string
}
```

The rendered form could be:

> Freedom of Information Act 2000, s.36(2)(b)(i)

with a link to the authoritative legislation.

There will also be case law and potentially official guidance, so the eventual model should probably accommodate different types of legal authority.

## Example formal bundle output

The generated plain-text bundle might look approximately like:

```text
ALLEGATION 1
Suspected misconduct in public office by X

1. SUMMARY
...

2. RELEVANT DUTY
D1 — ...
Authority:
...

3. ELEMENT: PUBLIC OFFICER

Proposition P1:
...

Evidence:
E1 — ...

4. ELEMENT: WILFUL CONDUCT

Proposition P2:
...

Evidence:
...

5. SERIOUSNESS

...

6. CONTRARY EVIDENCE

...

7. INVESTIGATION REQUESTED

...
```

This is only illustrative. The exact bundle format is not yet decided.

## React interface

The graph would be the primary interactive interface.

Possible interaction:

* click an Allegation
* see its Legal Elements
* expand those into Propositions
* expand propositions into supporting/contradicting Evidence
* click Evidence
* open the underlying Document/source at the relevant page/location
* inspect extracted text and/or screenshot
* navigate back through the provenance chain

Different node types could have different visual representations.

The goal is **not a generic mind-map application**. The typed relationships and evidential provenance are the important part.

## What I want to work on next

Please don't jump straight into React components or database tables.

I want to work through the **domain model first**, probably resulting in a reasonably clean TypeScript model containing perhaps 10–15 types/interfaces plus a well-defined set of relationships.

In particular, I want to investigate:

1. Whether the proposed node types are actually the right abstractions.
2. Which things should be nodes versus attributes.
3. Which relationships genuinely need to be first-class typed edges.
4. Whether `Evidence` should be a node in its own right or a property/attachment of a Proposition.
5. How `Inference` should work.
6. How to model contradictory evidence.
7. How source documents, extracted text, screenshots and precise source locations fit into the model.
8. How legal elements, duties and allegations should relate.
9. How to generate a coherent formal bundle from the graph without losing provenance.
10. How much of this should be general-purpose evidence management versus specifically oriented towards legal/governance investigations.

Please challenge the assumptions above rather than simply implementing them. I am more interested in getting the conceptual model right than in preserving the terminology I've proposed.
