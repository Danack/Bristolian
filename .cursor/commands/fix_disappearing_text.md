# Fix Disappearing Text

Fixes a frontend bug where pasted or typed text disappears from a controlled input or textarea when an unrelated rerender happens before the field loses focus.

## Usage

Pass a frontend file path that contains the problem, for example:

- `@fix_disappearing_text app/public/tsx/RoomLinkAddPanel.tsx`
- `@fix_disappearing_text app/public/tsx/RoomFilesPanel.tsx`

## Problem Pattern

This bug happens when all of these are true:

1. A field is a controlled input or textarea because its `value` comes from component state.
2. The field updates state with `onChange` instead of `onInput`.
3. A rerender happens before the field blurs, often because a login-status request or another store subscription finishes.
4. The rerender reapplies the old state value, so pasted or newly typed text disappears.

In this codebase the bug has already been seen when:

- a class component rerenders after `subscribe_logged_in(...)` fires
- a user pastes text into a focused field
- the field has not yet committed the new value into component state

## What To Check

When given a file:

1. Find controlled `<input>` and `<textarea>` elements whose `value` comes from component state.
2. Check whether they write state in `onChange` instead of `onInput`.
3. Check whether the component can rerender from unrelated state changes such as login status, polling, store subscriptions, parent rerenders, or network responses.
4. In class components, check whether login state is being read with a hook such as `use_logged_in()`. If so, replace that with the class-safe pattern using `get_logged_in()` for initial state and `subscribe_logged_in()` in lifecycle methods.

## Fix

Apply the smallest proven fix:

- For controlled text inputs and textareas, switch state updates from `onChange` to `onInput`.
- Preserve the existing state shape and validation behavior.
- If a class component is using `use_logged_in()` inside `render()`, replace it with:
  - initial state from `get_logged_in()`
  - a `subscribe_logged_in()` subscription in `componentDidMount()`
  - unsubscribe in `componentWillUnmount()`

Do not change fields like checkboxes or `datetime-local` inputs unless they show the same bug pattern. This command is specifically about text disappearing because state lags behind the DOM.

## Verification

After making the change:

1. Paste text into the affected field.
2. Keep focus in the field.
3. Trigger or wait for the rerender that used to wipe the text.
4. Confirm the text remains visible.
5. Run `ReadLints` on the edited file and fix any introduced issues.

## Expected Outcome

The field should keep pasted and typed text even if the component rerenders before blur, because the current value is committed to component state immediately on `input`.
