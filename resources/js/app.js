/**
 * Front-end behaviour ported from the React components.
 *
 * Each function below replaces a hook or piece of component state:
 *   revealSections  <- useInViewAnimation
 *   navigation      <- Navigation.tsx scroll/menu state
 *   carousel        <- Hero.tsx activeIndex + touch handlers
 *   glitch          <- NotFound.tsx glitchActive interval
 */

/**
 * Fade sections in as they enter the viewport (useInViewAnimation).
 *
 * The trigger is expressed as a viewport inset rather than IntersectionObserver's
 * `threshold`, because threshold is a fraction of *the element*: a long article
 * 10x the viewport height can never reach 0.15, so it would stay at opacity 0
 * forever. A negative bottom rootMargin fires once the element has entered by a
 * fifth of the viewport, whatever its height.
 */
function revealSections() {
  const sections = document.querySelectorAll('.reveal-section')

  if (!sections.length) {
    return
  }

  const revealAll = () =>
    sections.forEach((section) => section.setAttribute('data-in-view', 'true'))

  if (!('IntersectionObserver' in window)) {
    revealAll()
    return
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return
        }

        entry.target.setAttribute('data-in-view', 'true')
        observer.unobserve(entry.target)
      })
    },
    { threshold: 0, rootMargin: '0px 0px -20% 0px' },
  )

  sections.forEach((section) => {
    section.setAttribute('data-in-view', 'false')
    observer.observe(section)
  })

  // Last resort: never leave content hidden if the observer misbehaves.
  window.setTimeout(revealAll, 3000)
}

/**
 * Sticky header: hides on scroll down, reappears on scroll up, gains a
 * background past 50px, and drives the reading-progress bar.
 */
function navigation() {
  const nav = document.querySelector('[data-nav]')

  if (!nav) {
    return
  }

  const toggle = nav.querySelector('[data-nav-toggle]')
  const menu = nav.querySelector('[data-nav-menu]')
  const progress = nav.querySelector('[data-nav-progress]')
  const openIcon = nav.querySelector('[data-nav-icon-open]')
  const closeIcon = nav.querySelector('[data-nav-icon-close]')

  const scrolledClasses = ['bg-background/95', 'backdrop-blur-sm', 'border-b', 'border-border']
  let lastScrollY = window.scrollY
  let menuOpen = false

  const setMenu = (open) => {
    menuOpen = open

    menu?.classList.toggle('opacity-100', open)
    menu?.classList.toggle('opacity-0', !open)
    menu?.classList.toggle('pointer-events-none', !open)
    openIcon?.classList.toggle('hidden', open)
    closeIcon?.classList.toggle('hidden', !open)
    toggle?.setAttribute('aria-expanded', String(open))

    // Lock scrolling behind the overlay, as the React effect did.
    document.body.style.overflow = open ? 'hidden' : ''
    document.documentElement.style.overflow = open ? 'hidden' : ''
  }

  const onScroll = () => {
    const currentScrollY = window.scrollY
    const scrolled = currentScrollY > 50

    nav.classList.toggle('bg-transparent', !scrolled)
    scrolledClasses.forEach((className) => nav.classList.toggle(className, scrolled))

    const hidden = currentScrollY > lastScrollY && scrolled && !menuOpen
    nav.classList.toggle('-translate-y-[calc(100%-2px)]', hidden)

    lastScrollY = currentScrollY

    if (progress) {
      const { scrollHeight, clientHeight, scrollTop } = document.documentElement
      const scrollable = scrollHeight - clientHeight
      const ratio = scrollable > 0 ? scrollTop / scrollable : 0

      progress.style.width = `${Math.min(Math.max(ratio, 0), 1) * 100}%`
    }
  }

  toggle?.addEventListener('click', () => setMenu(!menuOpen))

  nav.querySelectorAll('[data-nav-link]').forEach((link) => {
    link.addEventListener('click', () => setMenu(false))
  })

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768 && menuOpen) {
      setMenu(false)
    }
  })

  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
}

/**
 * Hero project carousel.
 *
 * The positioning maths is copied from Hero.tsx so the deck stacks and
 * offsets identically, including its quirk of parking the "previous" card at
 * +200% once there are three or more projects.
 */
function carousel() {
  const root = document.querySelector('[data-carousel]')

  if (!root) {
    return
  }

  const slides = Array.from(root.querySelectorAll('[data-carousel-slide]'))
  const dots = Array.from(document.querySelectorAll('[data-carousel-dot]'))
  const total = slides.length

  if (total === 0) {
    return
  }

  let activeIndex = 0

  const render = () => {
    slides.forEach((slide, index) => {
      const offset = index - activeIndex
      const isActive = offset === 0
      const isPrev = offset === -1 || offset === total - 1
      const isNext = offset === 1 || offset === -total + 1

      if (!(isActive || isPrev || isNext)) {
        slide.style.display = 'none'
        return
      }

      let translateX = offset * 100
      if (offset < -1) translateX = -200
      if (offset > 1) translateX = 200

      slide.style.display = ''
      slide.style.transform = `translateX(${translateX}%) scale(${isActive ? 1 : 0.85})`
      slide.style.opacity = isActive ? '1' : '0.3'
      slide.style.zIndex = isActive ? '30' : isPrev || isNext ? '20' : '10'
      slide.style.pointerEvents = isActive ? 'auto' : 'none'

      slide.querySelector('[data-carousel-overlay]')?.classList.toggle('hidden', isActive)
    })

    dots.forEach((dot, index) => {
      const isActive = index === activeIndex

      dot.classList.toggle('w-8', isActive)
      dot.classList.toggle('bg-foreground', isActive)
      dot.classList.toggle('w-2', !isActive)
      dot.classList.toggle('bg-foreground/30', !isActive)
      dot.classList.toggle('hover:bg-foreground/50', !isActive)
      dot.setAttribute('aria-current', String(isActive))
    })
  }

  const change = (direction) => {
    const delta = direction === 'left' ? -1 : 1
    activeIndex = (activeIndex + delta + total) % total
    render()
  }

  document.querySelector('[data-carousel-prev]')?.addEventListener('click', () => change('left'))
  document.querySelector('[data-carousel-next]')?.addEventListener('click', () => change('right'))

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      activeIndex = index
      render()
    })
  })

  // Touch swipe, matching the 50px threshold from the React handlers.
  let touchStart = null

  root.addEventListener(
    'touchstart',
    (event) => {
      touchStart = event.targetTouches[0].clientX
    },
    { passive: true },
  )

  root.addEventListener(
    'touchend',
    (event) => {
      if (touchStart === null) {
        return
      }

      const distance = touchStart - event.changedTouches[0].clientX

      if (Math.abs(distance) > 50) {
        change(distance > 0 ? 'right' : 'left')
      }

      touchStart = null
    },
    { passive: true },
  )

  render()
}

/**
 * Periodic glitch offset on the 404 headline.
 */
function glitch() {
  const root = document.querySelector('[data-glitch]')

  if (!root) {
    return
  }

  const back = root.querySelector('[data-glitch-back]')
  const front = root.querySelector('[data-glitch-front]')

  setInterval(() => {
    back?.classList.add('translate-x-1')
    front?.classList.add('-translate-x-1', 'text-accent')

    setTimeout(() => {
      back?.classList.remove('translate-x-1')
      front?.classList.remove('-translate-x-1', 'text-accent')
    }, 200)
  }, 3000)
}

/**
 * Decorate article code blocks with a language label and a copy button.
 *
 * The React component baked this chrome into the HTML string it generated.
 * Doing it here instead keeps the stored post content plain <pre><code>, so it
 * stays editable in the block editor.
 */
function codeBlocks() {
  document.querySelectorAll('.article-content pre').forEach((pre) => {
    if (pre.parentElement?.dataset.codeBlock) {
      return
    }

    const code = pre.querySelector('code')
    const language =
      code?.className.replace('language-', '').trim() || 'plaintext'

    const container = document.createElement('div')
    container.dataset.codeBlock = 'true'
    container.className =
      'group relative my-6 rounded-xl overflow-hidden border border-border/40 bg-[#1e1e1e]'

    const header = document.createElement('div')
    header.className =
      'flex items-center justify-between px-4 py-2 bg-[#252526] border-b border-border/30'

    const label = document.createElement('span')
    label.className = 'text-xs font-mono text-muted-foreground'
    label.textContent = language

    const button = document.createElement('button')
    button.type = 'button'
    button.className =
      'flex items-center gap-2 px-3 py-1 rounded-md text-xs font-mono text-muted-foreground hover:text-foreground hover:bg-secondary/50 transition-colors'
    button.textContent = 'Copy'
    button.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(code?.textContent ?? '')
        button.textContent = 'Copied!'
        setTimeout(() => {
          button.textContent = 'Copy'
        }, 2000)
      } catch {
        // Clipboard unavailable — nothing useful to show.
      }
    })

    header.append(label, button)

    pre.replaceWith(container)
    container.append(header, pre)
  })
}

/**
 * "Go Back" on the 404 page and "Copy Link" on articles.
 */
function miscButtons() {
  document.querySelector('[data-history-back]')?.addEventListener('click', () => {
    window.history.back()
  })

  document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
      const url = button.getAttribute('data-copy-link')
      const original = button.textContent

      try {
        await navigator.clipboard.writeText(url)
        button.textContent = button.getAttribute('data-copied-label') || 'Copied!'
        setTimeout(() => {
          button.textContent = original
        }, 2000)
      } catch {
        // Clipboard access denied — leave the label untouched.
      }
    })
  })
}

const init = () => {
  revealSections()
  navigation()
  carousel()
  codeBlocks()
  glitch()
  miscButtons()
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init)
} else {
  init()
}

import.meta.hot?.accept()
