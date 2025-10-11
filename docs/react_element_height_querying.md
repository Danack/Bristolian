# Querying Element Heights in React/Preact

## Overview

In React/Preact, you can query an element's height using **refs** to access the underlying DOM element after it has been rendered.

## 1. Using Refs (Most Common)

Create a ref and attach it to the element, then query its dimensions after render:

```typescript
import { h, Component, createRef } from "preact";

// In your component class:
messageRef = createRef<HTMLDivElement>();

renderChatMessage(message: ChatMessage, index: number) {
    return <div className="user_container" key={index} ref={this.messageRef}>
        <div className="messages">Text is {message.text}</div>
        <span className="timestamp">{localTimeSimple(message.created_at)}</span>
    </div>;
}

// Then query the height:
componentDidMount() {
    if (this.messageRef.current) {
        const height = this.messageRef.current.offsetHeight;
        // or
        const rect = this.messageRef.current.getBoundingClientRect();
        console.log('Height:', rect.height);
    }
}
```

## 2. Callback Refs (For Multiple Elements)

Since you're rendering multiple messages, you might want to track each one:

```typescript
messageRefs: Map<number, HTMLDivElement> = new Map();

setMessageRef = (index: number) => (el: HTMLDivElement | null) => {
    if (el) {
        this.messageRefs.set(index, el);
        const height = el.offsetHeight;
        console.log(`Message ${index} height:`, height);
    }
};

renderChatMessage(message: ChatMessage, index: number) {
    return <div className="user_container" key={index} ref={this.setMessageRef(index)}>
        <div className="messages">Text is {message.text}</div>
        <span className="timestamp">{localTimeSimple(message.created_at)}</span>
    </div>;
}
```

## 3. Using useEffect Hook (Functional Components)

If using functional components instead of class components:

```typescript
import { h } from "preact";
import { useRef, useEffect } from "preact/hooks";

function ChatMessage({ message }) {
    const messageRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (messageRef.current) {
            const height = messageRef.current.offsetHeight;
            console.log('Message height:', height);
        }
    }, []); // Empty dependency array = run once after mount

    return (
        <div className="user_container" ref={messageRef}>
            <div className="messages">Text is {message.text}</div>
            <span className="timestamp">{localTimeSimple(message.created_at)}</span>
        </div>
    );
}
```

## Height Properties Available

Different properties provide different measurements:

- **`offsetHeight`** - Total height including padding, border, and scrollbar (most commonly used)
- **`clientHeight`** - Height including padding but excluding border and scrollbar
- **`scrollHeight`** - Total height of content including overflow (useful for scrollable containers)
- **`getBoundingClientRect().height`** - Precise floating-point height relative to viewport

## Common Use Cases

### Virtual Scrolling
Track heights of rendered items to calculate which items should be visible in viewport:

```typescript
componentDidUpdate() {
    this.messageRefs.forEach((element, index) => {
        const height = element.offsetHeight;
        // Store heights for virtual scroll calculations
    });
}
```

### Auto-scroll to Bottom (Chat)
Scroll to the bottom when new messages arrive:

```typescript
scrollToBottom() {
    if (this.messagesContainerRef.current) {
        const container = this.messagesContainerRef.current;
        container.scrollTop = container.scrollHeight;
    }
}

componentDidUpdate(prevProps, prevState) {
    if (prevState.messages.length < this.state.messages.length) {
        this.scrollToBottom();
    }
}
```

### Dynamic Layout Calculations
Calculate total height of multiple elements:

```typescript
getTotalMessagesHeight(): number {
    let total = 0;
    this.messageRefs.forEach((element) => {
        total += element.offsetHeight;
    });
    return total;
}
```

## Timing Considerations

- Heights are only available **after** the element is rendered to the DOM
- Use `componentDidMount()` for initial render measurements
- Use `componentDidUpdate()` for measurements after state/prop changes
- Consider using `requestAnimationFrame()` if layout hasn't settled:

```typescript
componentDidMount() {
    requestAnimationFrame(() => {
        if (this.messageRef.current) {
            const height = this.messageRef.current.offsetHeight;
            // Now layout has definitely settled
        }
    });
}
```

## Performance Notes

- Accessing layout properties (offsetHeight, getBoundingClientRect) can trigger browser reflow
- Batch reads together before any writes to minimize reflows
- Consider debouncing resize calculations
- For many elements, consider using IntersectionObserver or ResizeObserver APIs instead

