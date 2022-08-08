/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/danack-message/src/index.ts":
/*!**************************************************!*\
  !*** ./node_modules/danack-message/src/index.ts ***!
  \**************************************************/
/*! exports provided: registerMessageListener, unregisterListener, startMessageProcessing, stopMessageProcessing, timeoutDebugInfo, sendMessage, clearMessages, getQueuedMessages */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "registerMessageListener", function() { return registerMessageListener; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "unregisterListener", function() { return unregisterListener; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "startMessageProcessing", function() { return startMessageProcessing; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "stopMessageProcessing", function() { return stopMessageProcessing; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "timeoutDebugInfo", function() { return timeoutDebugInfo; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "sendMessage", function() { return sendMessage; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "clearMessages", function() { return clearMessages; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getQueuedMessages", function() { return getQueuedMessages; });
let messageProcessingActive = false;
let messageProcessingEverActive = false;
let notStartedWarningTimeout = null;
const notStartedWarningTime = 5;
let messageQueue = [];
const messsageListeners = {};
const registerMessageListener = (messageType, id, fn) => {
    if (messsageListeners[messageType] === undefined) {
        messsageListeners[messageType] = {};
    }
    messsageListeners[messageType][id] = fn;
};
const unregisterListener = (messageType, id) => {
    delete messsageListeners[messageType][id];
};
const startMessageProcessing = () => {
    if (notStartedWarningTimeout !== null) {
        clearTimeout(notStartedWarningTimeout);
        notStartedWarningTimeout = null;
    }
    messageProcessingActive = true;
    messageProcessingEverActive = true;
    while (messageQueue.length > 0) {
        const messageData = messageQueue.pop();
        if (messageData === undefined) {
            continue;
        }
        triggerMessageInternal(messageData.type, messageData.params);
    }
};
const stopMessageProcessing = () => {
    messageProcessingActive = false;
};
const timeoutDebugInfo = () => {
    console.warn('You sent a message but message processing has never been activated. Call Message.startMessageProcessing if you want events to be dispatched.');
};
function triggerMessageInternal(eventType, params) {
    if (messsageListeners[eventType] === undefined) {
        return;
    }
    const callbacks = messsageListeners[eventType];
    const keys = Object.keys(callbacks);
    for (const i in keys) {
        if (keys.hasOwnProperty(i) !== true) {
            continue;
        }
        const keyName = keys[i];
        const fn = callbacks[keyName];
        fn(params);
    }
}
const sendMessage = (eventType, params) => {
    if (messageProcessingActive === true) {
        return triggerMessageInternal(eventType, params);
    }
    messageQueue.push({ type: eventType, params });
    if (messageProcessingEverActive === true) {
        return;
    }
    notStartedWarningTimeout = setTimeout(timeoutDebugInfo, notStartedWarningTime * 1000);
};
const clearMessages = () => {
    messageQueue = [];
};
const getQueuedMessages = () => {
    return messageQueue;
};


/***/ }),

/***/ "./node_modules/preact/dist/preact.mjs":
/*!*********************************************!*\
  !*** ./node_modules/preact/dist/preact.mjs ***!
  \*********************************************/
/*! exports provided: default, h, createElement, cloneElement, createRef, Component, render, rerender, options */
/***/ (function(__webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "h", function() { return h; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createElement", function() { return h; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "cloneElement", function() { return cloneElement; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createRef", function() { return createRef; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Component", function() { return Component; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "rerender", function() { return rerender; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "options", function() { return options; });
var VNode = function VNode() {};

var options = {};

var stack = [];

var EMPTY_CHILDREN = [];

function h(nodeName, attributes) {
	var children = EMPTY_CHILDREN,
	    lastSimple,
	    child,
	    simple,
	    i;
	for (i = arguments.length; i-- > 2;) {
		stack.push(arguments[i]);
	}
	if (attributes && attributes.children != null) {
		if (!stack.length) stack.push(attributes.children);
		delete attributes.children;
	}
	while (stack.length) {
		if ((child = stack.pop()) && child.pop !== undefined) {
			for (i = child.length; i--;) {
				stack.push(child[i]);
			}
		} else {
			if (typeof child === 'boolean') child = null;

			if (simple = typeof nodeName !== 'function') {
				if (child == null) child = '';else if (typeof child === 'number') child = String(child);else if (typeof child !== 'string') simple = false;
			}

			if (simple && lastSimple) {
				children[children.length - 1] += child;
			} else if (children === EMPTY_CHILDREN) {
				children = [child];
			} else {
				children.push(child);
			}

			lastSimple = simple;
		}
	}

	var p = new VNode();
	p.nodeName = nodeName;
	p.children = children;
	p.attributes = attributes == null ? undefined : attributes;
	p.key = attributes == null ? undefined : attributes.key;

	if (options.vnode !== undefined) options.vnode(p);

	return p;
}

function extend(obj, props) {
  for (var i in props) {
    obj[i] = props[i];
  }return obj;
}

function applyRef(ref, value) {
  if (ref) {
    if (typeof ref == 'function') ref(value);else ref.current = value;
  }
}

var defer = typeof Promise == 'function' ? Promise.resolve().then.bind(Promise.resolve()) : setTimeout;

function cloneElement(vnode, props) {
  return h(vnode.nodeName, extend(extend({}, vnode.attributes), props), arguments.length > 2 ? [].slice.call(arguments, 2) : vnode.children);
}

var IS_NON_DIMENSIONAL = /acit|ex(?:s|g|n|p|$)|rph|ows|mnc|ntw|ine[ch]|zoo|^ord/i;

var items = [];

function enqueueRender(component) {
	if (!component._dirty && (component._dirty = true) && items.push(component) == 1) {
		(options.debounceRendering || defer)(rerender);
	}
}

function rerender() {
	var p;
	while (p = items.pop()) {
		if (p._dirty) renderComponent(p);
	}
}

function isSameNodeType(node, vnode, hydrating) {
	if (typeof vnode === 'string' || typeof vnode === 'number') {
		return node.splitText !== undefined;
	}
	if (typeof vnode.nodeName === 'string') {
		return !node._componentConstructor && isNamedNode(node, vnode.nodeName);
	}
	return hydrating || node._componentConstructor === vnode.nodeName;
}

function isNamedNode(node, nodeName) {
	return node.normalizedNodeName === nodeName || node.nodeName.toLowerCase() === nodeName.toLowerCase();
}

function getNodeProps(vnode) {
	var props = extend({}, vnode.attributes);
	props.children = vnode.children;

	var defaultProps = vnode.nodeName.defaultProps;
	if (defaultProps !== undefined) {
		for (var i in defaultProps) {
			if (props[i] === undefined) {
				props[i] = defaultProps[i];
			}
		}
	}

	return props;
}

function createNode(nodeName, isSvg) {
	var node = isSvg ? document.createElementNS('http://www.w3.org/2000/svg', nodeName) : document.createElement(nodeName);
	node.normalizedNodeName = nodeName;
	return node;
}

function removeNode(node) {
	var parentNode = node.parentNode;
	if (parentNode) parentNode.removeChild(node);
}

function setAccessor(node, name, old, value, isSvg) {
	if (name === 'className') name = 'class';

	if (name === 'key') {} else if (name === 'ref') {
		applyRef(old, null);
		applyRef(value, node);
	} else if (name === 'class' && !isSvg) {
		node.className = value || '';
	} else if (name === 'style') {
		if (!value || typeof value === 'string' || typeof old === 'string') {
			node.style.cssText = value || '';
		}
		if (value && typeof value === 'object') {
			if (typeof old !== 'string') {
				for (var i in old) {
					if (!(i in value)) node.style[i] = '';
				}
			}
			for (var i in value) {
				node.style[i] = typeof value[i] === 'number' && IS_NON_DIMENSIONAL.test(i) === false ? value[i] + 'px' : value[i];
			}
		}
	} else if (name === 'dangerouslySetInnerHTML') {
		if (value) node.innerHTML = value.__html || '';
	} else if (name[0] == 'o' && name[1] == 'n') {
		var useCapture = name !== (name = name.replace(/Capture$/, ''));
		name = name.toLowerCase().substring(2);
		if (value) {
			if (!old) node.addEventListener(name, eventProxy, useCapture);
		} else {
			node.removeEventListener(name, eventProxy, useCapture);
		}
		(node._listeners || (node._listeners = {}))[name] = value;
	} else if (name !== 'list' && name !== 'type' && !isSvg && name in node) {
		try {
			node[name] = value == null ? '' : value;
		} catch (e) {}
		if ((value == null || value === false) && name != 'spellcheck') node.removeAttribute(name);
	} else {
		var ns = isSvg && name !== (name = name.replace(/^xlink:?/, ''));

		if (value == null || value === false) {
			if (ns) node.removeAttributeNS('http://www.w3.org/1999/xlink', name.toLowerCase());else node.removeAttribute(name);
		} else if (typeof value !== 'function') {
			if (ns) node.setAttributeNS('http://www.w3.org/1999/xlink', name.toLowerCase(), value);else node.setAttribute(name, value);
		}
	}
}

function eventProxy(e) {
	return this._listeners[e.type](options.event && options.event(e) || e);
}

var mounts = [];

var diffLevel = 0;

var isSvgMode = false;

var hydrating = false;

function flushMounts() {
	var c;
	while (c = mounts.shift()) {
		if (options.afterMount) options.afterMount(c);
		if (c.componentDidMount) c.componentDidMount();
	}
}

function diff(dom, vnode, context, mountAll, parent, componentRoot) {
	if (!diffLevel++) {
		isSvgMode = parent != null && parent.ownerSVGElement !== undefined;

		hydrating = dom != null && !('__preactattr_' in dom);
	}

	var ret = idiff(dom, vnode, context, mountAll, componentRoot);

	if (parent && ret.parentNode !== parent) parent.appendChild(ret);

	if (! --diffLevel) {
		hydrating = false;

		if (!componentRoot) flushMounts();
	}

	return ret;
}

function idiff(dom, vnode, context, mountAll, componentRoot) {
	var out = dom,
	    prevSvgMode = isSvgMode;

	if (vnode == null || typeof vnode === 'boolean') vnode = '';

	if (typeof vnode === 'string' || typeof vnode === 'number') {
		if (dom && dom.splitText !== undefined && dom.parentNode && (!dom._component || componentRoot)) {
			if (dom.nodeValue != vnode) {
				dom.nodeValue = vnode;
			}
		} else {
			out = document.createTextNode(vnode);
			if (dom) {
				if (dom.parentNode) dom.parentNode.replaceChild(out, dom);
				recollectNodeTree(dom, true);
			}
		}

		out['__preactattr_'] = true;

		return out;
	}

	var vnodeName = vnode.nodeName;
	if (typeof vnodeName === 'function') {
		return buildComponentFromVNode(dom, vnode, context, mountAll);
	}

	isSvgMode = vnodeName === 'svg' ? true : vnodeName === 'foreignObject' ? false : isSvgMode;

	vnodeName = String(vnodeName);
	if (!dom || !isNamedNode(dom, vnodeName)) {
		out = createNode(vnodeName, isSvgMode);

		if (dom) {
			while (dom.firstChild) {
				out.appendChild(dom.firstChild);
			}
			if (dom.parentNode) dom.parentNode.replaceChild(out, dom);

			recollectNodeTree(dom, true);
		}
	}

	var fc = out.firstChild,
	    props = out['__preactattr_'],
	    vchildren = vnode.children;

	if (props == null) {
		props = out['__preactattr_'] = {};
		for (var a = out.attributes, i = a.length; i--;) {
			props[a[i].name] = a[i].value;
		}
	}

	if (!hydrating && vchildren && vchildren.length === 1 && typeof vchildren[0] === 'string' && fc != null && fc.splitText !== undefined && fc.nextSibling == null) {
		if (fc.nodeValue != vchildren[0]) {
			fc.nodeValue = vchildren[0];
		}
	} else if (vchildren && vchildren.length || fc != null) {
			innerDiffNode(out, vchildren, context, mountAll, hydrating || props.dangerouslySetInnerHTML != null);
		}

	diffAttributes(out, vnode.attributes, props);

	isSvgMode = prevSvgMode;

	return out;
}

function innerDiffNode(dom, vchildren, context, mountAll, isHydrating) {
	var originalChildren = dom.childNodes,
	    children = [],
	    keyed = {},
	    keyedLen = 0,
	    min = 0,
	    len = originalChildren.length,
	    childrenLen = 0,
	    vlen = vchildren ? vchildren.length : 0,
	    j,
	    c,
	    f,
	    vchild,
	    child;

	if (len !== 0) {
		for (var i = 0; i < len; i++) {
			var _child = originalChildren[i],
			    props = _child['__preactattr_'],
			    key = vlen && props ? _child._component ? _child._component.__key : props.key : null;
			if (key != null) {
				keyedLen++;
				keyed[key] = _child;
			} else if (props || (_child.splitText !== undefined ? isHydrating ? _child.nodeValue.trim() : true : isHydrating)) {
				children[childrenLen++] = _child;
			}
		}
	}

	if (vlen !== 0) {
		for (var i = 0; i < vlen; i++) {
			vchild = vchildren[i];
			child = null;

			var key = vchild.key;
			if (key != null) {
				if (keyedLen && keyed[key] !== undefined) {
					child = keyed[key];
					keyed[key] = undefined;
					keyedLen--;
				}
			} else if (min < childrenLen) {
					for (j = min; j < childrenLen; j++) {
						if (children[j] !== undefined && isSameNodeType(c = children[j], vchild, isHydrating)) {
							child = c;
							children[j] = undefined;
							if (j === childrenLen - 1) childrenLen--;
							if (j === min) min++;
							break;
						}
					}
				}

			child = idiff(child, vchild, context, mountAll);

			f = originalChildren[i];
			if (child && child !== dom && child !== f) {
				if (f == null) {
					dom.appendChild(child);
				} else if (child === f.nextSibling) {
					removeNode(f);
				} else {
					dom.insertBefore(child, f);
				}
			}
		}
	}

	if (keyedLen) {
		for (var i in keyed) {
			if (keyed[i] !== undefined) recollectNodeTree(keyed[i], false);
		}
	}

	while (min <= childrenLen) {
		if ((child = children[childrenLen--]) !== undefined) recollectNodeTree(child, false);
	}
}

function recollectNodeTree(node, unmountOnly) {
	var component = node._component;
	if (component) {
		unmountComponent(component);
	} else {
		if (node['__preactattr_'] != null) applyRef(node['__preactattr_'].ref, null);

		if (unmountOnly === false || node['__preactattr_'] == null) {
			removeNode(node);
		}

		removeChildren(node);
	}
}

function removeChildren(node) {
	node = node.lastChild;
	while (node) {
		var next = node.previousSibling;
		recollectNodeTree(node, true);
		node = next;
	}
}

function diffAttributes(dom, attrs, old) {
	var name;

	for (name in old) {
		if (!(attrs && attrs[name] != null) && old[name] != null) {
			setAccessor(dom, name, old[name], old[name] = undefined, isSvgMode);
		}
	}

	for (name in attrs) {
		if (name !== 'children' && name !== 'innerHTML' && (!(name in old) || attrs[name] !== (name === 'value' || name === 'checked' ? dom[name] : old[name]))) {
			setAccessor(dom, name, old[name], old[name] = attrs[name], isSvgMode);
		}
	}
}

var recyclerComponents = [];

function createComponent(Ctor, props, context) {
	var inst,
	    i = recyclerComponents.length;

	if (Ctor.prototype && Ctor.prototype.render) {
		inst = new Ctor(props, context);
		Component.call(inst, props, context);
	} else {
		inst = new Component(props, context);
		inst.constructor = Ctor;
		inst.render = doRender;
	}

	while (i--) {
		if (recyclerComponents[i].constructor === Ctor) {
			inst.nextBase = recyclerComponents[i].nextBase;
			recyclerComponents.splice(i, 1);
			return inst;
		}
	}

	return inst;
}

function doRender(props, state, context) {
	return this.constructor(props, context);
}

function setComponentProps(component, props, renderMode, context, mountAll) {
	if (component._disable) return;
	component._disable = true;

	component.__ref = props.ref;
	component.__key = props.key;
	delete props.ref;
	delete props.key;

	if (typeof component.constructor.getDerivedStateFromProps === 'undefined') {
		if (!component.base || mountAll) {
			if (component.componentWillMount) component.componentWillMount();
		} else if (component.componentWillReceiveProps) {
			component.componentWillReceiveProps(props, context);
		}
	}

	if (context && context !== component.context) {
		if (!component.prevContext) component.prevContext = component.context;
		component.context = context;
	}

	if (!component.prevProps) component.prevProps = component.props;
	component.props = props;

	component._disable = false;

	if (renderMode !== 0) {
		if (renderMode === 1 || options.syncComponentUpdates !== false || !component.base) {
			renderComponent(component, 1, mountAll);
		} else {
			enqueueRender(component);
		}
	}

	applyRef(component.__ref, component);
}

function renderComponent(component, renderMode, mountAll, isChild) {
	if (component._disable) return;

	var props = component.props,
	    state = component.state,
	    context = component.context,
	    previousProps = component.prevProps || props,
	    previousState = component.prevState || state,
	    previousContext = component.prevContext || context,
	    isUpdate = component.base,
	    nextBase = component.nextBase,
	    initialBase = isUpdate || nextBase,
	    initialChildComponent = component._component,
	    skip = false,
	    snapshot = previousContext,
	    rendered,
	    inst,
	    cbase;

	if (component.constructor.getDerivedStateFromProps) {
		state = extend(extend({}, state), component.constructor.getDerivedStateFromProps(props, state));
		component.state = state;
	}

	if (isUpdate) {
		component.props = previousProps;
		component.state = previousState;
		component.context = previousContext;
		if (renderMode !== 2 && component.shouldComponentUpdate && component.shouldComponentUpdate(props, state, context) === false) {
			skip = true;
		} else if (component.componentWillUpdate) {
			component.componentWillUpdate(props, state, context);
		}
		component.props = props;
		component.state = state;
		component.context = context;
	}

	component.prevProps = component.prevState = component.prevContext = component.nextBase = null;
	component._dirty = false;

	if (!skip) {
		rendered = component.render(props, state, context);

		if (component.getChildContext) {
			context = extend(extend({}, context), component.getChildContext());
		}

		if (isUpdate && component.getSnapshotBeforeUpdate) {
			snapshot = component.getSnapshotBeforeUpdate(previousProps, previousState);
		}

		var childComponent = rendered && rendered.nodeName,
		    toUnmount,
		    base;

		if (typeof childComponent === 'function') {

			var childProps = getNodeProps(rendered);
			inst = initialChildComponent;

			if (inst && inst.constructor === childComponent && childProps.key == inst.__key) {
				setComponentProps(inst, childProps, 1, context, false);
			} else {
				toUnmount = inst;

				component._component = inst = createComponent(childComponent, childProps, context);
				inst.nextBase = inst.nextBase || nextBase;
				inst._parentComponent = component;
				setComponentProps(inst, childProps, 0, context, false);
				renderComponent(inst, 1, mountAll, true);
			}

			base = inst.base;
		} else {
			cbase = initialBase;

			toUnmount = initialChildComponent;
			if (toUnmount) {
				cbase = component._component = null;
			}

			if (initialBase || renderMode === 1) {
				if (cbase) cbase._component = null;
				base = diff(cbase, rendered, context, mountAll || !isUpdate, initialBase && initialBase.parentNode, true);
			}
		}

		if (initialBase && base !== initialBase && inst !== initialChildComponent) {
			var baseParent = initialBase.parentNode;
			if (baseParent && base !== baseParent) {
				baseParent.replaceChild(base, initialBase);

				if (!toUnmount) {
					initialBase._component = null;
					recollectNodeTree(initialBase, false);
				}
			}
		}

		if (toUnmount) {
			unmountComponent(toUnmount);
		}

		component.base = base;
		if (base && !isChild) {
			var componentRef = component,
			    t = component;
			while (t = t._parentComponent) {
				(componentRef = t).base = base;
			}
			base._component = componentRef;
			base._componentConstructor = componentRef.constructor;
		}
	}

	if (!isUpdate || mountAll) {
		mounts.push(component);
	} else if (!skip) {

		if (component.componentDidUpdate) {
			component.componentDidUpdate(previousProps, previousState, snapshot);
		}
		if (options.afterUpdate) options.afterUpdate(component);
	}

	while (component._renderCallbacks.length) {
		component._renderCallbacks.pop().call(component);
	}if (!diffLevel && !isChild) flushMounts();
}

function buildComponentFromVNode(dom, vnode, context, mountAll) {
	var c = dom && dom._component,
	    originalComponent = c,
	    oldDom = dom,
	    isDirectOwner = c && dom._componentConstructor === vnode.nodeName,
	    isOwner = isDirectOwner,
	    props = getNodeProps(vnode);
	while (c && !isOwner && (c = c._parentComponent)) {
		isOwner = c.constructor === vnode.nodeName;
	}

	if (c && isOwner && (!mountAll || c._component)) {
		setComponentProps(c, props, 3, context, mountAll);
		dom = c.base;
	} else {
		if (originalComponent && !isDirectOwner) {
			unmountComponent(originalComponent);
			dom = oldDom = null;
		}

		c = createComponent(vnode.nodeName, props, context);
		if (dom && !c.nextBase) {
			c.nextBase = dom;

			oldDom = null;
		}
		setComponentProps(c, props, 1, context, mountAll);
		dom = c.base;

		if (oldDom && dom !== oldDom) {
			oldDom._component = null;
			recollectNodeTree(oldDom, false);
		}
	}

	return dom;
}

function unmountComponent(component) {
	if (options.beforeUnmount) options.beforeUnmount(component);

	var base = component.base;

	component._disable = true;

	if (component.componentWillUnmount) component.componentWillUnmount();

	component.base = null;

	var inner = component._component;
	if (inner) {
		unmountComponent(inner);
	} else if (base) {
		if (base['__preactattr_'] != null) applyRef(base['__preactattr_'].ref, null);

		component.nextBase = base;

		removeNode(base);
		recyclerComponents.push(component);

		removeChildren(base);
	}

	applyRef(component.__ref, null);
}

function Component(props, context) {
	this._dirty = true;

	this.context = context;

	this.props = props;

	this.state = this.state || {};

	this._renderCallbacks = [];
}

extend(Component.prototype, {
	setState: function setState(state, callback) {
		if (!this.prevState) this.prevState = this.state;
		this.state = extend(extend({}, this.state), typeof state === 'function' ? state(this.state, this.props) : state);
		if (callback) this._renderCallbacks.push(callback);
		enqueueRender(this);
	},
	forceUpdate: function forceUpdate(callback) {
		if (callback) this._renderCallbacks.push(callback);
		renderComponent(this, 2);
	},
	render: function render() {}
});

function render(vnode, parent, merge) {
  return diff(merge, vnode, {}, false, parent, false);
}

function createRef() {
	return {};
}

var preact = {
	h: h,
	createElement: h,
	cloneElement: cloneElement,
	createRef: createRef,
	Component: Component,
	render: render,
	rerender: rerender,
	options: options
};

/* harmony default export */ __webpack_exports__["default"] = (preact);

//# sourceMappingURL=preact.mjs.map


/***/ }),

/***/ "./node_modules/widgety/src/index.ts":
/*!*******************************************!*\
  !*** ./node_modules/widgety/src/index.ts ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
const setupWidgetForElement = (element, component, h, render) => {
    let params = {};
    if (element.dataset.hasOwnProperty('widgety_json') === true) {
        const json = element.dataset.widgety_json;
        if (json !== undefined) {
            params = JSON.parse(json);
        }
    }
    const reactType = h(component, params);
    render(reactType, element);
};
const setupWidget = (widgetBindings, h, render) => {
    const domElements = document.getElementsByClassName(widgetBindings.class);
    const domElementsSnapshot = [];
    for (let i = 0; i < domElements.length; i += 1) {
        domElementsSnapshot.push(domElements.item(i));
    }
    for (const j in domElementsSnapshot) {
        if (domElementsSnapshot.hasOwnProperty(j) !== true) {
            continue;
        }
        const element = domElementsSnapshot[j];
        setupWidgetForElement(element, widgetBindings.component, h, render);
    }
};
const initByClass = (widgetBindings, h, render) => {
    for (const widgetBinding of widgetBindings) {
        setupWidget(widgetBinding, h, render);
    }
};
/* harmony default export */ __webpack_exports__["default"] = (initByClass);


/***/ }),

/***/ "./public/tsx/CSPViolationReportsPanel.tsx":
/*!*************************************************!*\
  !*** ./public/tsx/CSPViolationReportsPanel.tsx ***!
  \*************************************************/
/*! exports provided: CSPViolationReportsPanel */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "CSPViolationReportsPanel", function() { return CSPViolationReportsPanel; });
/* harmony import */ var preact__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! preact */ "./node_modules/preact/dist/preact.mjs");

let api_url = "http://local.api.bristolian.org";
let REPORTS_SHOWN_PER_PAGE = 10;
function getDefaultState(props) {
    return {
        current_page: 0,
        csp_reports: convertJsonToCSPViolationReport(props.initial_json_data),
        csp_report_count: getCountFromInfo(props.initial_json_data)
    };
}
function convertDataToCspReport(data) {
    var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l;
    let csp_violation_report = {
        document_uri: (_a = data['document-uri']) !== null && _a !== void 0 ? _a : null,
        referrer: (_b = data['referrer']) !== null && _b !== void 0 ? _b : null,
        blocked_uri: (_c = data['blocked-uri']) !== null && _c !== void 0 ? _c : null,
        violated_directive: (_d = data['violated-directive']) !== null && _d !== void 0 ? _d : null,
        original_policy: (_e = data['original-policy']) !== null && _e !== void 0 ? _e : null,
        disposition: (_f = data['disposition']) !== null && _f !== void 0 ? _f : null,
        effective_directive: (_g = data['effective-directive']) !== null && _g !== void 0 ? _g : null,
        line_number: (_h = data['line-number']) !== null && _h !== void 0 ? _h : null,
        script_sample: (_j = data['script-sample']) !== null && _j !== void 0 ? _j : null,
        source_file: (_k = data['source-file']) !== null && _k !== void 0 ? _k : null,
        status_code: (_l = data['status-code']) !== null && _l !== void 0 ? _l : null,
    };
    return csp_violation_report;
}
function getCountFromInfo(array_of_data) {
    let count = array_of_data['count'];
    return count;
}
function convertJsonToCSPViolationReport(array_of_data) {
    let csp_reports = [];
    let reports = array_of_data['reports'];
    for (let datum in reports) {
        let csp_report = convertDataToCspReport(reports[datum]);
        csp_reports.push(csp_report);
    }
    return csp_reports;
}
class CSPViolationReportsPanel extends preact__WEBPACK_IMPORTED_MODULE_0__["Component"] {
    constructor(props) {
        super(props);
        this.state = getDefaultState(props);
    }
    componentDidMount() {
    }
    componentWillUnmount() {
    }
    renderCSPReport(csp_report, index) {
        return Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("tr", { key: index },
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.document_uri),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.referrer),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.blocked_uri),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.violated_directive),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.original_policy),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.disposition),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.effective_directive),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.line_number),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.script_sample),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.source_file),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("td", null, csp_report.status_code));
    }
    renderCSPReportsTableBody() {
        return Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("tbody", null,
            this.state.csp_reports.map(this.renderCSPReport),
            " ");
    }
    renderSelectorOptions(csp_report_count) {
        let option_array = [];
        let max_page_for_selector = csp_report_count / REPORTS_SHOWN_PER_PAGE;
        if (max_page_for_selector > REPORTS_SHOWN_PER_PAGE) {
            max_page_for_selector = REPORTS_SHOWN_PER_PAGE;
        }
        for (let i = 0; i < max_page_for_selector; i += 1) {
            option_array.push(Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("option", { value: "{i}" }, i + 1));
        }
        return option_array;
    }
    processCspPageData(selected_index, data) {
        let new_state = {
            current_page: selected_index,
            csp_reports: convertJsonToCSPViolationReport(data),
            csp_report_count: getCountFromInfo(data)
        };
        this.setState(new_state);
    }
    updatePageSelector(event) {
        let selected_index = event.target.selectedIndex;
        console.log("Need to fetch page " + selected_index);
        let url = api_url + '/system/csp/reports_for_page?page=' + selected_index;
        fetch(url)
            .then(response => response.json())
            .then(data => this.processCspPageData(selected_index, data))
            .catch((error) => {
            console.log(error);
        });
    }
    renderCSPReportsPageSelector() {
        let textblock = Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("span", null,
            "Number of CSP reports: ",
            this.state.csp_report_count);
        if (this.state.csp_report_count <= REPORTS_SHOWN_PER_PAGE) {
            return Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("div", null, textblock);
        }
        let selector_options = this.renderSelectorOptions(this.state.csp_report_count);
        let selector = Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("div", null,
            "Page select:",
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("select", { name: "page", onChange: (e) => this.updatePageSelector(e) }, selector_options));
        return Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("div", null,
            textblock,
            selector);
    }
    render(props, state) {
        if (this.state.csp_reports.length == 0) {
            return Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("span", null, "No csp reports");
        }
        let csp_reports_table_body = this.renderCSPReportsTableBody();
        let csp_reports_selector = this.renderCSPReportsPageSelector();
        return Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("div", null,
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("div", null, csp_reports_selector),
            Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("table", null,
                Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("thead", null,
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "document_uri"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "referrer"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "blocked_uri"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "violated_directive"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "original_policy"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "disposition"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "effective_directive"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "line_number"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "script_sample"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "source_file"),
                    Object(preact__WEBPACK_IMPORTED_MODULE_0__["h"])("th", null, "status_code")),
                csp_reports_table_body));
    }
}


/***/ }),

/***/ "./public/tsx/bootstrap.tsx":
/*!**********************************!*\
  !*** ./public/tsx/bootstrap.tsx ***!
  \**********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var preact__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! preact */ "./node_modules/preact/dist/preact.mjs");
/* harmony import */ var _CSPViolationReportsPanel__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CSPViolationReportsPanel */ "./public/tsx/CSPViolationReportsPanel.tsx");
/* harmony import */ var widgety__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! widgety */ "./node_modules/widgety/src/index.ts");
/* harmony import */ var danack_message__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! danack-message */ "./node_modules/danack-message/src/index.ts");




let panels = [
    {
        class: 'widget_csp_violation_reports',
        component: _CSPViolationReportsPanel__WEBPACK_IMPORTED_MODULE_1__["CSPViolationReportsPanel"]
    },
];
Object(widgety__WEBPACK_IMPORTED_MODULE_2__["default"])(panels, preact__WEBPACK_IMPORTED_MODULE_0__["h"], preact__WEBPACK_IMPORTED_MODULE_0__["render"]);
Object(danack_message__WEBPACK_IMPORTED_MODULE_3__["startMessageProcessing"])();
console.log("Bootstrap finished");


/***/ }),

/***/ 0:
/*!****************************************!*\
  !*** multi ./public/tsx/bootstrap.tsx ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./public/tsx/bootstrap.tsx */"./public/tsx/bootstrap.tsx");


/***/ })

/******/ });
//# sourceMappingURL=app.bundle.map