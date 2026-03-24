<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import type { CSSProperties } from 'vue'

const props = withDefaults(
    defineProps<{
        canRegister?: boolean
        heroTitle?: string
        heroBodyText?: string
        heroBgImage?: string | null
        heroFloatingImages?: (string | null)[]
        servicesIntroText?: string
        services?: Array<{
            name: string
            description: string
            tags: string[]
            mediaUrl: string | null
            mediaType?: 'image' | 'video'
        }>
        teamAvatars?: string[]
        cases?: Array<{ name: string; slug: string; video: string | null; photo: string | null }>
        testimonialQuote?: string
        testimonialAuthor?: string
        testimonialAuthorAvatar?: string | null
        testimonialLogo?: string | null
        clientLogos?: string[]
        teamDescription?: string
        teamButtonText?: string
        teamButtonHref?: string
        teamPhotos?: string[]
    }>(),
    {
        canRegister: true,
        heroTitle: 'Credible Creativity',
        heroBodyText:
            "People love brands that live up to what they say and do. Now more than ever. We help your brand uncover its true essence and turn it into a credible, compelling story. One that creates consistently engaging experiences, wows minds, and wins hearts. From A to Z. From customers to employees. That is how you deliver on your brand promise. It's what we love to do, and what we call Credible Creativity.",
        heroBgImage: null,
        heroFloatingImages: () => [null, null, null, null],
        servicesIntroText:
            'Credible Creativity delivers on your brand promise through three connected services that engage everyone from customer to employee.',
        services: () => [
            {
                name: 'Brand',
                description:
                    'Tell your credible brand story, stand out in your market, and connect with your customers.',
                tags: ['Positioning', 'Narrative', 'Campaigns', 'Visual identity', 'Activations', 'Social'],
                mediaUrl: null,
                mediaType: 'image',
            },
            {
                name: 'Experience',
                description:
                    'Create memorable, connected brand experiences that drive engagement and loyalty from A to Z, customer to employee.',
                tags: ['Customer Journey', 'Digital', 'Events', 'Environments'],
                mediaUrl: null,
                mediaType: 'image',
            },
            {
                name: 'Employee',
                description:
                    'Align your internal culture with your external promise. Create energy and trust inside your company and shape how it\'s seen from the outside.',
                tags: ['Internal Comms', 'Culture', 'Engagement', 'Training'],
                mediaUrl: null,
                mediaType: 'image',
            },
        ],
        teamAvatars: () => [],
        cases: () => [
            { name: 'VisitTwente', slug: 'visittwente', video: null, photo: null },
            { name: 'Veloretti', slug: 'veloretti', video: null, photo: null },
            { name: 'Delta Air Lines', slug: 'delta-air-lines', video: null, photo: null },
            { name: 'Nuon/Vattenfall', slug: 'nuon-vattenfall', video: null, photo: null },
            { name: 'Knaap', slug: 'knaap', video: null, photo: null },
            { name: 'Hamburg Commercial Bank', slug: 'hamburg-commercial-bank', video: null, photo: null },
            { name: 'TakeAway', slug: 'takeaway', video: null, photo: null },
        ],
        testimonialQuote: () =>
            '"Our people live the brand, passengers feel it instantly, journeys become memorable and your airline becomes unmistakable."',
        testimonialAuthor: 'Michelle',
        testimonialAuthorAvatar: () => '/figma-assets/8a2fda0a-bf49-4b4c-b9a8-e66c433eed09.png',
        testimonialLogo: () => '/figma-assets/e6fdd583-cf9b-4016-89eb-8a00b937a435.png',
        clientLogos: () => [
            '/figma-assets/72b82c06-224e-447c-a6e3-63cf81af6fbf.png',
            '/figma-assets/e6fdd583-cf9b-4016-89eb-8a00b937a435.png',
            '/figma-assets/51c72f3c-4a09-491a-a4cd-629926c6994e.png',
            '/figma-assets/25863eb3-6397-486a-a0c1-d8fe79dfe8dd.png',
            '/figma-assets/d11446f1-3384-40e5-82f1-ef19ebdc26e4.png',
        ],
        teamDescription: () =>
            'Our reception desk is a tea bar – and occasionally just a bar. We host workshops sessions and always have bar stools reserved for our clients and the talented, inspiring people from our Lemon agency community.',
        teamButtonText: 'About us',
        teamButtonHref: '#about',
        teamPhotos: () => [],
    },
)

// ─── Figma assets (geldig 7 dagen) ────────────────────────────────────────────
const SCROLL_INDICATOR_URL = '/figma-assets/6eea3ed9-667d-4b4e-8eb4-acc6dce262db.svg'
const CASES_ARROW_URL = '/figma-assets/d80e54ef-48d6-43a4-8629-0801cfe41603.svg'
const HERO_MEET_AVATARS = [
    '/figma-assets/1c7ada78-2639-466d-9c86-b655a62ed8d4.png',
    '/figma-assets/f43a4ed5-5188-4fec-83e4-0c7d3aee6388.png',
    '/figma-assets/8b7bd57d-2be8-461e-8982-ebe28d772ec9.png',
    '/figma-assets/00ba73c5-9c31-4545-912f-957df2c1d581.png',
]
const FIGMA_DESKTOP_WIDTH = 1640
const FIGMA_DESKTOP_HEIGHT = 900

// ─── State ────────────────────────────────────────────────────────────────────
const windowScrollY = ref(0)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : FIGMA_DESKTOP_WIDTH)
const windowHeight = ref(typeof window !== 'undefined' ? window.innerHeight : FIGMA_DESKTOP_HEIGHT)

// ─── Section refs ─────────────────────────────────────────────────────────────
const heroRef = ref<HTMLElement | null>(null)
const aboutRef = ref<HTMLElement | null>(null)
const aboutTextRef = ref<HTMLElement | null>(null)
const heroBodyTextRef = ref<HTMLElement | null>(null)
const servicesRef = ref<HTMLElement | null>(null)
const casesRef = ref<HTMLElement | null>(null)
const testimonialRef = ref<HTMLElement | null>(null)
const teamRef = ref<HTMLElement | null>(null)

// ─── Progress values (0–1) ────────────────────────────────────────────────────
const heroProgress = ref(0)
const servicesProgress = ref(0)
const casesProgress = ref(0)
const testimonialProgress = ref(0)
const teamProgress = ref(0)

// ─── Scroll handler ───────────────────────────────────────────────────────────
function updateProgress(sectionEl: HTMLElement, progress: { value: number }) {
    const scrolled = window.scrollY - sectionEl.offsetTop
    const maxScroll = sectionEl.offsetHeight - window.innerHeight
    progress.value = Math.max(0, Math.min(scrolled / maxScroll, 1))
}

function onScroll() {
    windowScrollY.value = window.scrollY
    if (heroRef.value) updateProgress(heroRef.value, heroProgress)
    if (servicesRef.value) updateProgress(servicesRef.value, servicesProgress)
    if (casesRef.value) updateProgress(casesRef.value, casesProgress)
    if (testimonialRef.value) updateProgress(testimonialRef.value, testimonialProgress)
    if (teamRef.value) updateProgress(teamRef.value, teamProgress)
}

function onResize() {
    windowWidth.value = window.innerWidth
    windowHeight.value = window.innerHeight
    onScroll()
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onResize, { passive: true })
    onScroll()
    carouselRaf = requestAnimationFrame(carouselTick)
})
onUnmounted(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', onResize)
    if (carouselRaf !== null) cancelAnimationFrame(carouselRaf)
})

// ─── Animation helpers ────────────────────────────────────────────────────────
function rp(val: number, from: number, to: number) {
    return Math.max(0, Math.min((val - from) / (to - from), 1))
}
function ease(t: number) {
    return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t
}

function lerp(from: number, to: number, t: number) {
    return from + (to - from) * t
}

function sx(value: number) {
    return `${(value / FIGMA_DESKTOP_WIDTH) * windowWidth.value}px`
}

function sy(value: number) {
    return `${(value / FIGMA_DESKTOP_HEIGHT) * windowHeight.value}px`
}

function rightPx(left: number, width: number) {
    const remaining = FIGMA_DESKTOP_WIDTH - left - width
    return sx(remaining)
}

const p = heroProgress
const HERO_TEXT_LOCK_HEIGHT = 249.5
const HERO_TEXT_LOCK_WIDTH = 1169.4
const HERO_TEXT_LOCK_FONT_SIZE = 48.03
const HERO_TEXT_LOCK_LINE_HEIGHT = 60
const HERO_TEXT_LOCK_LETTER_SPACING = -1.44
const HERO_VIDEO_EXPAND_END = 0.10
const HERO_TEXT_START_TOP = 744
const HERO_TEXT_LOCK_PROGRESS = HERO_VIDEO_EXPAND_END
const HERO_TEXT_SCALE_START = 0.065
const HERO_TEXT_SCALE_END = 0.095

// ─── Nav kleur ────────────────────────────────────────────────────────────────
const navOnYellow = computed(() => {
    if (servicesProgress.value > 0.02 && servicesProgress.value < 0.99) return false
    if (casesProgress.value > 0.02 && casesProgress.value < 0.99) return false
    if (testimonialProgress.value > 0.02 && testimonialProgress.value < 0.99) return false
    return heroProgress.value >= 1
})

// ─── Hero animaties ───────────────────────────────────────────────────────────

// Helper: detect video by extension
function isVideo(url: string | null | undefined): boolean {
    return !!url && /\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(url)
}

// Hero sticky overlay: transparent background (video is a separate fixed element), z-index above video
const heroBgStyle = computed(() => ({ zIndex: 3 }))

// Hero video: always position:fixed covering the full viewport (z-index 2, behind overlay content).
// A clip-path reveals only the card area initially and expands to full screen on scroll.
// State 1 (node 1:4):  clip = 716×419 at left:calc(16.67%+186.5px), top:201px, radius:20px
// State 2 (node 1:104): clip = none (full viewport)
const heroVideoStyle = computed(() => {
    if (servicesProgress.value >= 1 && casesProgressRaw.value >= 0) return { display: 'none' as const }
    const e = 1 - ease(rp(p.value, 0, 0.10))
    const left = (459.8364 / FIGMA_DESKTOP_WIDTH) * windowWidth.value
    const top = (208.7368 / FIGMA_DESKTOP_HEIGHT) * windowHeight.value
    const width = (720.3271 / FIGMA_DESKTOP_WIDTH) * windowWidth.value
    const height = (426.9324 / FIGMA_DESKTOP_HEIGHT) * windowHeight.value
    const right = Math.max(0, windowWidth.value - left - width)
    const bottom = Math.max(0, windowHeight.value - top - height)
    const clipPath = e <= 0
        ? 'none'
        : `inset(${(top * e).toFixed(1)}px ${(right * e).toFixed(1)}px ${(bottom * e).toFixed(1)}px ${(left * e).toFixed(1)}px round ${(20 * e).toFixed(1)}px)`
    return {
        position: 'fixed' as const,
        top: '0',
        left: '0',
        width: '100vw',
        height: '100vh',
        zIndex: 2,
        overflow: 'hidden' as const,
        clipPath,
        pointerEvents: 'none' as const,
    }
})

function floatingBaseStyle(index: number) {
    const cards = [
        { width: 476, height: 278.888, left: 944.955, top: -168.811 },
        { width: 477, height: 279.474, left: 110.446, top: -196.814 },
        { width: 407.18, height: 238.566, left: 1430.007, top: 542.01 },
        { width: 463.915, height: 271.807, left: 0, top: 780 },
    ]

    const card = cards[index] ?? cards[0]

    return {
        width: sx(card.width),
        height: sy(card.height),
        left: sx(card.left),
        top: sy(card.top),
        overflow: 'hidden' as const,
        filter: 'blur(17px)',
    }
}

// 4 side cards (nodes 1:7–1:14): scatter off-screen when card 0 expands
// indices 0–3 map to top-right, top-left, bottom-right, bottom-left
function floatingStyle(index: number) {
    const t = ease(rp(p.value, 0, 0.10))
    const cards = [
        { x: 195,  y: -231, rotate: 1.98 },
        { x: -335, y: -330, rotate: 2.16 },
        { x: 386,  y: 405,  rotate: 1.41 },
        { x: -270, y: 275,  rotate: 0 },
    ]
    const c = cards[index] ?? { x: 0, y: 0, rotate: 0 }
    return {
        opacity: 1 - t,
        transform: `translate(${(t * c.x).toFixed(1)}px, ${(t * c.y).toFixed(1)}px) rotate(${c.rotate}deg)`,
    }
}

// Title: 120px → 82.493px as card expands (node 1:4 → 1:104), then scrolls off (node 1:104 → 1:155)
const titleStyle = computed(() => {
    const phase1 = ease(rp(p.value, 0, 0.10))     // title shrinks as video expands
    const phase2 = ease(rp(p.value, 0.10, 0.20))  // title snel uit beeld
    return {
        fontSize: `${(120 - 37.507 * phase1).toFixed(2)}px`,
        lineHeight: `${(70 - 21.879 * phase1).toFixed(2)}px`,
        letterSpacing: `${(-4.8 + 1.5003 * phase1).toFixed(4)}px`,
        transform: `translateY(${(28 * phase1 - 553 * phase2).toFixed(1)}px)`,
        opacity: 1 - ease(rp(p.value, 0.13, 0.20)),
    }
})

// Hero body text: staat vast op zijn startpositie; alleen schaal verandert tijdens de video-expansie.
const bodyTextExitT = computed(() => ease(rp(p.value, 0.995, 1)))
const bodyTextScaleT = computed(() => ease(rp(p.value, HERO_TEXT_SCALE_START, HERO_TEXT_SCALE_END)))
const bodyTextFontSize = computed(() => {
    if (p.value >= HERO_TEXT_SCALE_END) return `${HERO_TEXT_LOCK_FONT_SIZE}px`
    return `${lerp(29.407, HERO_TEXT_LOCK_FONT_SIZE, bodyTextScaleT.value).toFixed(2)}px`
})
const bodyTextLineHeight = computed(() => {
    if (p.value >= HERO_TEXT_SCALE_END) return `${HERO_TEXT_LOCK_LINE_HEIGHT}px`
    return `${lerp(36.759, HERO_TEXT_LOCK_LINE_HEIGHT, bodyTextScaleT.value).toFixed(2)}px`
})
const bodyTextLetterSpacing = computed(() => {
    if (p.value >= HERO_TEXT_SCALE_END) return `${HERO_TEXT_LOCK_LETTER_SPACING}px`
    return `${lerp(-0.8822, HERO_TEXT_LOCK_LETTER_SPACING, bodyTextScaleT.value).toFixed(4)}px`
})
const bodyTextWidth = computed(() => {
    if (p.value >= HERO_TEXT_SCALE_END) return `min(${sx(HERO_TEXT_LOCK_WIDTH)}, 90vw)`
    const phaseAWidth = lerp(701.36, HERO_TEXT_LOCK_WIDTH, bodyTextScaleT.value)
    return `min(${sx(phaseAWidth)}, 90vw)`
})
const bodyTextWindowHeight = computed(() => {
    if (p.value >= HERO_TEXT_SCALE_END) return `${HERO_TEXT_LOCK_HEIGHT}px`
    return `${lerp(186, HERO_TEXT_LOCK_HEIGHT, bodyTextScaleT.value).toFixed(1)}px`
})
const bodyTextTop = computed(() => {
    const baseTop = (HERO_TEXT_START_TOP / FIGMA_DESKTOP_HEIGHT) * windowHeight.value
    const t = rp(p.value, 0.085, 0.990)
    const endTop = -(HERO_TEXT_LOCK_HEIGHT + 40)
    return `${lerp(baseTop, endTop, t).toFixed(1)}px`
})
const bodyTextOpacity = computed(() => {
    if (p.value <= 0.995) return 1
    return 1 - bodyTextExitT.value
})
const bodyTextInnerOffset = computed(() => {
    return '0px'
})
// Full-viewport container met gradient mask (viewport-relatief: 30% boven/onder)
const bodyTextContainerStyle = computed<CSSProperties>(() => {
    const gradient = 'linear-gradient(180deg, transparent 0%, black 30%, black 70%, transparent 100%)'
    return {
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        pointerEvents: 'none',
        opacity: bodyTextOpacity.value,
        zIndex: 3,
        maskImage: gradient,
        WebkitMaskImage: gradient,
    }
})
// Inner wrapper: positioneert de tekst op de juiste viewport-positie
const bodyTextInnerStyle = computed<CSSProperties>(() => ({
    position: 'absolute',
    left: '50%',
    top: bodyTextTop.value,
    width: bodyTextWidth.value,
    overflow: 'visible',
    transform: 'translate(-50%, 0)',
}))
const scrollIndicatorOpacity = computed(() => 1 - ease(rp(p.value, 0.95, 1.0)))

function goToServices() {
    document.getElementById('services')?.scrollIntoView({ behavior: 'smooth' })
}

const meetTeamStyle = computed(() => ({
    opacity: Math.min(ease(rp(p.value, 0.40, 0.52)), 1 - ease(rp(p.value, 0.90, 0.98))),
    transform: `translateY(${(1 - rp(p.value, 0.40, 0.52)) * 24}px)`,
}))

// ─── Services ─────────────────────────────────────────────────────────────────
const servicesLeadProgress = computed(() => {
    const aboutEl = aboutRef.value
    const sectionTop = servicesRef.value?.offsetTop ?? Infinity
    const viewportTop = windowScrollY.value
    const aboutBottom = aboutEl ? aboutEl.offsetTop + aboutEl.offsetHeight : sectionTop
    const start = aboutBottom - windowHeight.value * 1.2
    const end = sectionTop + windowHeight.value * 0.08

    return rp(viewportTop, start, end)
})

const SERVICE_SCROLL_LOCK_POINTS = [0.235, 0.555, 0.805] as const
const SERVICE_SCROLL_LOCK_SPAN = 0.04

function withScrollLocks(progress: number, points: readonly number[], span: number): number {
    const safeProgress = Math.max(0, Math.min(progress, 1))
    const totalHold = Math.min(span * points.length, 0.9)
    const moveScale = 1 / (1 - totalHold)
    let cursor = 0
    let moved = 0

    for (const point of points) {
        const start = Math.max(cursor, point - span / 2)
        const end = Math.min(1, start + span)

        if (safeProgress < start) {
            return Math.max(0, Math.min((moved + (safeProgress - cursor)) * moveScale, 1))
        }

        moved += start - cursor

        if (safeProgress <= end) {
            return Math.max(0, Math.min(moved * moveScale, 1))
        }

        cursor = end
    }

    return Math.max(0, Math.min((moved + (safeProgress - cursor)) * moveScale, 1))
}

const servicesVisualProgress = computed(() =>
    withScrollLocks(servicesProgress.value, SERVICE_SCROLL_LOCK_POINTS, SERVICE_SCROLL_LOCK_SPAN),
)

let lastServicesDebugBucket = -1
let lastHeroDebugBucket = -1
let lastTeamDebugBucket = -1

watch([windowScrollY, servicesLeadProgress, servicesProgress, servicesVisualProgress], ([scrollY, lp, sp, svp]) => {
    if (typeof window === 'undefined') return

    const bucket = Math.round(sp * 100)
    if (bucket === lastServicesDebugBucket) return

    lastServicesDebugBucket = bucket
    console.log('[services-scroll]', {
        scrollY: Math.round(scrollY),
        leadProgress: Number(lp.toFixed(3)),
        servicesProgress: Number(sp.toFixed(3)),
        servicesVisualProgress: Number(svp.toFixed(3)),
        fullscreenTriggerRaw: Number(rp(sp, 0.03, 0.16).toFixed(3)),
    })
})

watch(
    [windowScrollY, heroProgress, bodyTextTop, bodyTextWindowHeight, bodyTextInnerOffset, bodyTextExitT],
    ([scrollY, hp, top, windowH, innerOffset, exitT]) => {
        if (typeof window === 'undefined') return

        const bucket = Math.round(hp * 100)
        if (bucket === lastHeroDebugBucket) return

        lastHeroDebugBucket = bucket
        console.log('[hero-scroll]', {
            scrollY: Math.round(scrollY),
            heroProgress: Number(hp.toFixed(3)),
            bodyTextTop: Number.parseFloat(top),
            bodyTextWindowHeight: Number.parseFloat(windowH),
            bodyTextInnerOffset: Number.parseFloat(innerOffset),
            bodyTextExitT: Number(exitT.toFixed(3)),
        })
    },
)

watch([windowScrollY, teamProgress], ([scrollY, tp]) => {
    if (typeof window === 'undefined') return

    const bucket = Math.round(tp * 100)
    if (bucket === lastTeamDebugBucket) return

    lastTeamDebugBucket = bucket
    console.log('[team-scroll]', {
        scrollY: Math.round(scrollY),
        teamProgress: Number(tp.toFixed(3)),
    })
})

// Video: position:fixed (zoals hero), clip-path clipt van gecentreerde kaart → fullscreen
// Fase 0.00–0.12: expansie, 0.12–0.45: Brand, 0.45–0.78: Experience, 0.78–1.00: Employee
const servicesVideoStyle = computed(() => {
    const sp = servicesVisualProgress.value
    const lp = servicesLeadProgress.value
    if (lp <= 0 && sp <= 0) return { display: 'none' as const }
    if (sp >= 1 && casesProgressRaw.value >= 0) return { display: 'none' as const }

    const leadInT = ease(rp(lp, 0, 0.72))
    const leadOpacity = ease(rp(lp, 0.05, 0.28))
    const fullscreenT = ease(rp(sp, 0.03, 0.065))
    const textBottomViewport = aboutTextRef.value
        ? aboutTextRef.value.offsetTop + aboutTextRef.value.offsetHeight - windowScrollY.value
        : (194 + 297)
    const targetTop = textBottomViewport + 110
    const startWidth = windowWidth.value * 0.8
    const startLeft = (windowWidth.value - startWidth) / 2
    const startHeight = Math.min((860.054 / FIGMA_DESKTOP_HEIGHT) * windowHeight.value, windowHeight.value * 0.58)
    const startTop = windowHeight.value + 32
    const settledTop = Math.max(targetTop, windowHeight.value - startHeight - 24)
    const enterTop = lerp(startTop, settledTop, leadInT)

    const top = lerp(enterTop, 0, fullscreenT)
    const left = lerp(startLeft, 0, fullscreenT)
    const width = lerp(startWidth, windowWidth.value, fullscreenT)
    const height = lerp(startHeight, windowHeight.value, fullscreenT)
    const radius = lerp(20, 0, fullscreenT)
    const exitT = ease(rp(sp, 0.965, 1))
    const exitY = exitT * windowHeight.value

    return {
        position: 'fixed' as const,
        top: `${(top - exitY).toFixed(1)}px`,
        left: `${left.toFixed(1)}px`,
        width: `${width.toFixed(1)}px`,
        height: `${height.toFixed(1)}px`,
        zIndex: 4,
        borderRadius: `${radius.toFixed(1)}px`,
        overflow: 'hidden' as const,
        pointerEvents: 'none' as const,
        transform: 'translateY(0)',
        transformOrigin: 'center top',
        transition: 'top 0.15s linear, left 0.15s linear, width 0.15s linear, height 0.15s linear, opacity 0.2s linear, border-radius 0.15s linear',
        opacity: Math.min(
            Math.max(leadOpacity, ease(rp(sp, 0.02, 0.12))),
            1 - ease(rp(sp, 0.985, 1)),
        ),
    }
})
// Gele achtergrond: apart fixed element op z-index 3 (onder video at 4, boven hero-video at 2)
// Start zodra de services-sectie de viewport inkomt (van onderaf), zodat de overgang
// van de intro-sectie (ook geel) naadloos aansluit — geen donkere flash tussendoor.
const servicesBgStyle = computed(() => {
    const sp = servicesVisualProgress.value
    const lp = servicesLeadProgress.value
    const exitT = ease(rp(sp, 0.965, 1))
    if (sp >= 1.0) return { display: 'none' as const }
    if (lp <= 0 && sp <= 0) {
        return { display: 'none' as const }
    }
    return {
        position: 'fixed' as const,
        top: `${(-exitT * windowHeight.value).toFixed(1)}px`,
        left: '0',
        width: '100vw',
        height: '100vh',
        background: '#ffc700',
        zIndex: 3,
        pointerEvents: 'none' as const,
        opacity: ease(rp(Math.max(lp, sp), 0, 0.18)),
    }
})
// Text-overlay: fixed, z-index 6 — boven video (4) en gele bg (3)
// Sticky werkt niet betrouwbaar binnen stacking contexts; fixed garandeert correcte positie.
const servicesTextStyle = computed(() => {
    const sp = servicesVisualProgress.value
    const exitT = ease(rp(sp, 0.965, 1))
    if (sp <= 0) return { display: 'none' as const }
    return {
        position: 'fixed' as const,
        top: `${(-exitT * windowHeight.value).toFixed(1)}px`,
        left: '0',
        width: '100vw',
        height: '100vh',
        zIndex: 6,
        pointerEvents: 'none' as const,
        background: 'linear-gradient(180deg, rgba(0, 0, 0, 0.00) 35.16%, rgba(0, 0, 0, 0.20) 78.29%)',
        opacity: Math.min(
            ease(rp(sp, 0.10, 0.22)),     // fade in zodra video fullscreen is
            1 - ease(rp(sp, 0.985, 1.0)), // fade out bij einde services
        ),
    }
})
const servicesScrollIndicatorOpacity = computed(() => Math.min(
    ease(rp(servicesVisualProgress.value, 0.14, 0.25)),
    1 - ease(rp(servicesVisualProgress.value, 0.72, 0.82)),
))
const activeServiceIndex = computed(() => {
    const t = servicesVisualProgress.value
    if (t < 0.45) return 0
    if (t < 0.78) return 1
    return 2
})

// Cases parallax entrance: sticky div gedraagt zich als fixed tijdens instap
const casesProgressRaw = computed(() => {
    if (!casesRef.value) return 0
    const scrollY = windowScrollY.value
    const scrollHeight = casesRef.value.offsetHeight - windowHeight.value
    if (scrollHeight <= 0) return 0
    return (scrollY - casesRef.value.offsetTop) / scrollHeight
})

const casesStickyStyle = computed(() => {
    return {}
})

// ─── Cases auto-scroll carousel ───────────────────────────────────────────────
const CARD_SIZES = [
    { width: 658, height: 426 },
    { width: 527, height: 454 },
    { width: 530, height: 358 },
] as const

const CARD_GAP = 32

const hoveredCaseIndex = ref<number | null>(null)
const caseVideoRefs = ref<Map<number, HTMLVideoElement>>(new Map())

const casesDisplayItems = computed(() => {
    const items = props.cases ?? []
    return [...items, ...items]
})

const casesTrackOffset = computed(() => {
    const items = props.cases ?? []
    const totalWidth = items.reduce((sum, _, i) => sum + CARD_SIZES[i % CARD_SIZES.length].width, 0)
    return totalWidth + items.length * CARD_GAP
})

// Carousel RAF scroll + drag
const carouselOffset = ref(0)
const carouselDragging = ref(false)
let carouselRaf: number | null = null
let carouselLastTime = 0
let carouselDragStartX = 0
let carouselDragStartOffset = 0
let carouselVelocity = 0
let carouselLastDragX = 0
let carouselLastDragTime = 0

const CAROUSEL_SPEED = 60 // px/s
const TESTIMONIAL_LOGO_GAP = 40
const TESTIMONIAL_LOGO_SPEED = 36
const testimonialLogoOffset = ref(0)

const testimonialLogoSize = computed(() =>
    Math.max(140, Math.min(300, Math.round(windowWidth.value * 0.14))),
)

const testimonialLogoRowHeight = computed(() => testimonialLogoSize.value + 12)

const testimonialLogoItems = computed(() => {
    const logos = props.clientLogos ?? []
    return [...logos, ...logos, ...logos]
})

const testimonialLogoTrackWidth = computed(() => {
    const logos = props.clientLogos ?? []
    if (logos.length === 0) return 0
    return logos.length * testimonialLogoSize.value + Math.max(logos.length - 1, 0) * TESTIMONIAL_LOGO_GAP
})

const testimonialLogoTrackStyle = computed(() => ({
    transform: `translateX(${(-testimonialLogoTrackWidth.value + testimonialLogoOffset.value).toFixed(1)}px)`,
    gap: `${TESTIMONIAL_LOGO_GAP}px`,
    willChange: 'transform' as const,
}))

function carouselTick(timestamp: number): void {
    const dt = carouselLastTime ? (timestamp - carouselLastTime) / 1000 : 0
    carouselLastTime = timestamp
    const total = casesTrackOffset.value
    if (total > 0) {
        if (!carouselDragging.value && hoveredCaseIndex.value === null) {
            carouselOffset.value = (carouselOffset.value + CAROUSEL_SPEED * dt) % total
        } else if (!carouselDragging.value && Math.abs(carouselVelocity) > 1) {
            carouselVelocity *= 0.90
            carouselOffset.value = ((carouselOffset.value + carouselVelocity * dt) % total + total) % total
        }
    }

    const logoTotal = testimonialLogoTrackWidth.value
    if (logoTotal > 0) {
        testimonialLogoOffset.value = (testimonialLogoOffset.value + TESTIMONIAL_LOGO_SPEED * dt) % logoTotal
    }

    carouselRaf = requestAnimationFrame(carouselTick)
}

function onCarouselPointerDown(e: PointerEvent): void {
    carouselDragging.value = true
    carouselDragStartX = e.clientX
    carouselDragStartOffset = carouselOffset.value
    carouselVelocity = 0
    carouselLastDragX = e.clientX
    carouselLastDragTime = performance.now()
    ;(e.currentTarget as HTMLElement).setPointerCapture(e.pointerId)
}

function onCarouselPointerMove(e: PointerEvent): void {
    if (!carouselDragging.value) return
    const delta = carouselDragStartX - e.clientX
    const total = casesTrackOffset.value
    carouselOffset.value = ((carouselDragStartOffset + delta) % total + total) % total
    const now = performance.now()
    const dt = (now - carouselLastDragTime) / 1000
    if (dt > 0) {
        carouselVelocity = (carouselLastDragX - e.clientX) / dt
        carouselLastDragX = e.clientX
        carouselLastDragTime = now
    }
}

function onCarouselPointerUp(): void {
    carouselDragging.value = false
}

const casesTrackStyle = computed(() => ({
    transform: `translateX(-${carouselOffset.value.toFixed(1)}px)`,
    cursor: carouselDragging.value ? 'grabbing' : 'grab',
    willChange: 'transform' as const,
    userSelect: 'none' as const,
}))

function caseCardSize(displayIndex: number) {
    const i = displayIndex % Math.max(props.cases?.length ?? 1, 1)
    return CARD_SIZES[i % CARD_SIZES.length]
}

function isCaseHovered(displayIndex: number): boolean {
    const i = displayIndex % Math.max(props.cases?.length ?? 1, 1)
    return hoveredCaseIndex.value === i
}

function setCaseVideoRef(el: HTMLVideoElement | null, displayIndex: number): void {
    if (el) caseVideoRefs.value.set(displayIndex, el)
    else caseVideoRefs.value.delete(displayIndex)
}

function onCaseMouseEnter(displayIndex: number): void {
    const i = displayIndex % Math.max(props.cases?.length ?? 1, 1)
    hoveredCaseIndex.value = i
    caseVideoRefs.value.forEach((video, idx) => {
        if (idx % Math.max(props.cases?.length ?? 1, 1) === i) video.play()
    })
}

function onCaseMouseLeave(displayIndex: number): void {
    const i = displayIndex % Math.max(props.cases?.length ?? 1, 1)
    if (hoveredCaseIndex.value === i) hoveredCaseIndex.value = null
    caseVideoRefs.value.forEach((video, idx) => {
        if (idx % Math.max(props.cases?.length ?? 1, 1) === i) {
            video.pause()
            video.currentTime = 0
        }
    })
}

// ─── Testimonial animaties ────────────────────────────────────────────────────
const testimonialEntryProgress = computed(() => {
    if (!testimonialRef.value) return 0

    const rect = testimonialRef.value.getBoundingClientRect()
    return rp(windowHeight.value * 0.98 - rect.top, 0, windowHeight.value * 0.45)
})

const testimonialQuoteStyle = computed(() => ({
    opacity: 1,
    transform: 'translateY(0)',
}))

const clientLogosStyle = computed(() => ({
    opacity: 1,
    transform: 'translateY(0)',
}))

function shiftTestimonialLogos(direction: 'prev' | 'next') {
    const total = testimonialLogoTrackWidth.value
    if (total <= 0) return

    const delta = testimonialLogoSize.value + TESTIMONIAL_LOGO_GAP
    const nextOffset = direction === 'next'
        ? testimonialLogoOffset.value + delta
        : testimonialLogoOffset.value - delta

    testimonialLogoOffset.value = ((nextOffset % total) + total) % total
}

// ─── Team animaties ───────────────────────────────────────────────────────────
// Positions as % of Figma viewport (1640 × 900) so they scale with any screen.
// top/height use vh, left/width use vw-percentages.
const TEAM_PHOTO_POSITIONS = [
    { left: '11.8%', top: '20.8vh', width: '11.9%', height: '29vh', rotate: 2.66 },
    { left: '67.4%', top: '4.3vh', width: '13%', height: '32.1vh', rotate: 1.87 },
    { left: '92.3%', top: '37.4vh', width: '9.4%', height: '22.8vh', rotate: 0 },
    { left: '-1.2%', top: '53.7vh', width: '13.9%', height: '36vh', rotate: -2.14 },
    { left: '69.8%', top: 'calc(65vh + 50px)', width: '16.2%', height: '39.6vh', rotate: -2.67 },
]

// Subtiele, continue upward drift vanaf teamProgress 0.
// Iedere foto beweegt net iets anders voor een natuurlijker parallax-effect.
const TEAM_PHOTO_DRIFT_VH = [0.05, 0.065, 0.08, 0.095, 0.11]

function teamPhotoStyle(index: number) {
    const pos = TEAM_PHOTO_POSITIONS[index]
    const dist = (TEAM_PHOTO_DRIFT_VH[index] ?? 0.08) * windowHeight.value
    const enter = ease(rp(teamProgress.value, 0, 0.08))
    const drift = ease(rp(teamProgress.value, 0, 1))
    const offsetY = (1 - enter) * 18
    return {
        opacity: 1,
        transform: `translate3d(0, ${offsetY - drift * dist}px, 0) rotate(${pos?.rotate ?? 0}deg)`,
        willChange: 'transform',
        backfaceVisibility: 'hidden',
    }
}

// "Team Lemon" text exits slower than photos
const teamTextExitStyle = computed(() => {
    const enter = ease(rp(teamProgress.value, 0, 0.08))
    const drift = teamProgress.value
    return {
        opacity: 1,
        transform: `translate3d(0, ${(1 - enter) * 28 - drift * windowHeight.value * 0.035}px, 0)`,
        willChange: 'transform',
        backfaceVisibility: 'hidden',
    }
})

const teamContentStyle = computed(() => ({
    opacity: 1,
    transform: 'translateY(0)',
}))
</script>

<template>
    <Head title="Lemon Scented Tea — Credible Creativity" />

    <SiteHeader :nav-on-yellow="navOnYellow" />

    <!-- ═══════════════════════════════════════════════
         HERO VIDEO — fixed, z-index 2, clip-path zoomt van kaart naar volledig scherm
    ════════════════════════════════════════════════ -->
    <div :style="heroVideoStyle">
        <video
            v-if="heroBgImage && isVideo(heroBgImage)"
            :src="heroBgImage"
            autoplay loop muted playsinline
            class="h-full w-full object-cover"
        />
        <img v-else-if="heroBgImage" :src="heroBgImage" alt="" class="h-full w-full object-cover" />
        <div v-else class="flex h-full w-full items-center justify-center bg-neutral-800">
            <span class="text-sm text-white/20">Hoofdvideo</span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         SERVICES VIDEO — fixed, z-index 4, clip-path van kaart → fullscreen
    ════════════════════════════════════════════════ -->
    <div :style="servicesVideoStyle">
        <template v-for="(service, i) in services" :key="service.name + '-fixed'">
            <div
                class="absolute inset-0"
                :style="{ opacity: activeServiceIndex === i ? 1 : 0, transition: 'opacity 0.8s ease' }"
            >
                <video
                    v-if="service.mediaType === 'video' && service.mediaUrl"
                    :src="service.mediaUrl"
                    autoplay loop muted playsinline
                    class="h-full w-full object-cover"
                />
                <img
                    v-else-if="service.mediaType === 'image' && service.mediaUrl"
                    :src="service.mediaUrl"
                    :alt="service.name"
                    class="h-full w-full object-cover"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center"
                    :class="[i === 0 ? 'bg-neutral-800' : i === 1 ? 'bg-neutral-700' : 'bg-neutral-600']"
                >
                    <span class="text-sm text-white/20">{{ service.name }} media</span>
                </div>
            </div>
        </template>
    </div>

    <!-- ═══════════════════════════════════════════════
         SERVICES GELE ACHTERGROND — fixed, z-index 3
         Zichtbaar in de clip-path gebieden rondom de video-kaart.
         Lager dan de video (4) maar hoger dan de hero-video (2).
    ════════════════════════════════════════════════ -->
    <div :style="servicesBgStyle" />

    <!-- ═══════════════════════════════════════════════
         SERVICES TEXT OVERLAY — fixed, z-index 6, boven video (4) en gele bg (3)
         Altijd zichtbaar op de juiste positie ongeacht scroll — geen sticky nodig.
    ════════════════════════════════════════════════ -->
    <div :style="servicesTextStyle">

        <!-- "Services" label — geel, Figma top: 54.56vh -->
        <p
            class="absolute text-[#ffc700]"
            style="
                left: 59px;
                top: 54.56vh;
                font-family: 'Avenir', sans-serif;
                font-size: 26px;
                font-weight: 400;
                line-height: 60px;
                letter-spacing: -0.78px;
            "
        >
            Services
        </p>

        <!-- Servicenamen — absoluut gestapeld (Figma: 59.44vh + i×8.9vh) -->
        <p
            v-for="(service, i) in services"
            :key="service.name + '-name'"
            class="absolute text-white transition-all duration-500"
            :style="{
                left: '59px',
                top: `${59.44 + i * 8.9}vh`,
                fontFamily: '\'Avenir\', sans-serif',
                fontWeight: '900',
                fontStyle: 'oblique',
                fontSize: '86px',
                lineHeight: '141.295px',
                letterSpacing: '-2.58px',
                opacity: activeServiceIndex === i ? 1 : 0.08,
            }"
        >
            {{ service.name }}
        </p>

        <!-- Beschrijving + tags — rechtsonder, wisselend per actieve service -->
        <template v-for="(service, i) in services" :key="service.name + '-right'">
            <div
                class="absolute right-[59px]"
                style="bottom: 88px; max-width: 559px"
                :style="{
                    opacity: activeServiceIndex === i ? 1 : 0,
                    transform: `translateY(${activeServiceIndex === i ? 0 : 20}px)`,
                    transition: 'opacity 0.7s ease, transform 0.7s ease',
                }"
            >
                <p
                    class="mb-5 text-white"
                    style="
                        font-family: 'Avenir', sans-serif;
                        font-size: 22px;
                        font-weight: 400;
                        line-height: 32px;
                        letter-spacing: -0.22px;
                    "
                >
                    {{ service.description }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="tag in service.tags"
                        :key="tag"
                        class="rounded-[40px] border border-white px-4 py-1.5 text-white"
                        style="
                            font-family: 'Avenir', sans-serif;
                            font-size: 14px;
                            font-weight: 400;
                            line-height: 24px;
                            letter-spacing: -0.14px;
                        "
                    >
                        {{ tag }}
                    </span>
                </div>
            </div>
        </template>

        <!-- Scroll indicator — gecentreerd onderin (zelfde als Figma 1:376) -->
        <div
            class="absolute left-1/2 -translate-x-1/2"
            style="bottom: 52px"
            :style="{ opacity: servicesScrollIndicatorOpacity }"
        >
            <div
                class="flex h-[65px] w-[65px] items-center justify-center rounded-full"
                style="
                    backdrop-filter: blur(6px);
                    border: 0.687px solid rgba(255,255,255,0.3);
                    box-shadow: 0px 11.5px 19.8px 0px rgba(0,0,0,0.05);
                "
            >
                <img :src="SCROLL_INDICATOR_URL" alt="Scroll" style="width: 38px; height: 26px; object-fit: contain" />
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         HERO — 550vh sticky
    ════════════════════════════════════════════════ -->
    <section id="home" ref="heroRef" class="bg-[#0c0c0c]" style="height: 550vh">
        <div class="sticky top-0 h-screen overflow-hidden" :style="heroBgStyle">

            <!-- Kaarten 1–4: scatter bij scroll (indices 0–3 in floatingStyle) -->
            <!-- Kaart 1: top-right (node 1:7) -->
            <div class="absolute rounded-[20px]" :style="{ ...floatingBaseStyle(0), ...floatingStyle(0) }">
                <video v-if="heroFloatingImages?.[0] && isVideo(heroFloatingImages[0])" :src="heroFloatingImages[0]!" autoplay loop muted playsinline class="h-full w-full object-cover" />
                <img v-else-if="heroFloatingImages?.[0]" :src="heroFloatingImages[0]!" class="h-full w-full object-cover" alt="" />
                <div v-else class="h-full w-full bg-neutral-800" />
            </div>
            <!-- Kaart 2: top-left (node 1:9) -->
            <div class="absolute rounded-[20px]" :style="{ ...floatingBaseStyle(1), ...floatingStyle(1) }">
                <video v-if="heroFloatingImages?.[1] && isVideo(heroFloatingImages[1])" :src="heroFloatingImages[1]!" autoplay loop muted playsinline class="h-full w-full object-cover" />
                <img v-else-if="heroFloatingImages?.[1]" :src="heroFloatingImages[1]!" class="h-full w-full object-cover" alt="" />
                <div v-else class="h-full w-full bg-neutral-800" />
            </div>
            <!-- Kaart 3: bottom-right (node 1:11) -->
            <div class="absolute rounded-[20px]" :style="{ ...floatingBaseStyle(2), ...floatingStyle(2) }">
                <video v-if="heroFloatingImages?.[2] && isVideo(heroFloatingImages[2])" :src="heroFloatingImages[2]!" autoplay loop muted playsinline class="h-full w-full object-cover" />
                <img v-else-if="heroFloatingImages?.[2]" :src="heroFloatingImages[2]!" class="h-full w-full object-cover" alt="" />
                <div v-else class="h-full w-full bg-neutral-700" />
            </div>
            <!-- Kaart 4: bottom-left (node 1:13) -->
            <div class="absolute rounded-[20px]" :style="{ ...floatingBaseStyle(3), ...floatingStyle(3) }">
                <video v-if="heroFloatingImages?.[3] && isVideo(heroFloatingImages[3])" :src="heroFloatingImages[3]!" autoplay loop muted playsinline class="h-full w-full object-cover" />
                <img v-else-if="heroFloatingImages?.[3]" :src="heroFloatingImages[3]!" class="h-full w-full object-cover" alt="" />
                <div v-else class="h-full w-full bg-neutral-800" />
            </div>

            <!-- Titel: 120px→82px terwijl kaart expandeert, scrollt daarna weg (node 1:4→1:104→1:155) -->
            <div class="pointer-events-none absolute left-0 right-0" :style="{ top: sy(387) }">
                <h1
                    class="text-center"
                    style="
                        font-family: 'Avenir', system-ui, sans-serif;
                        font-weight: 900;
                        font-style: oblique;
                        color: #ffc700;
                    "
                    :style="titleStyle"
                >
                    {{ heroTitle }}
                </h1>
            </div>

            <!-- Bodytekst: full-viewport container met gradient mask (viewport-relatief 30% boven/onder) -->
            <div :style="bodyTextContainerStyle">
                <div :style="bodyTextInnerStyle">
                    <p
                        ref="heroBodyTextRef"
                        class="text-center"
                        :style="{
                            fontFamily: '\'Avenir\', system-ui, sans-serif',
                            fontWeight: '400',
                            fontSize: bodyTextFontSize,
                            lineHeight: bodyTextLineHeight,
                            letterSpacing: bodyTextLetterSpacing,
                            color: 'white',
                            transform: `translateY(-${bodyTextInnerOffset})`,
                        }"
                    >
                        {{ heroBodyText }}
                    </p>
                </div>
            </div>

            <!-- Scroll indicator: ring vult zich naarmate heroProgress vordert; klik → services -->
            <button
                class="cursor-pointer"
                :style="{ position: 'fixed', left: sx(819.667), top: sy(764), transform: 'translateX(-50%)', opacity: scrollIndicatorOpacity, background: 'none', border: 'none', padding: 0, zIndex: 3 }"
                @click="goToServices"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="105" height="127" viewBox="0 0 105 127" fill="none">
                    <g filter="url(#filter_hero_scroll)">
                        <rect x="20.1202" y="30.3433" width="64.3133" height="64.3133" rx="32.1567" stroke="white" stroke-opacity="0.3" stroke-width="0.686695" shape-rendering="crispEdges"/>
                    </g>
                    <path d="M45.4885 66.0251L52.7772 73.3138L60.3922 65.6988" stroke="#FFC700" stroke-width="1.53846"/>
                    <path d="M52.777 72.8786V50.6863" stroke="#FFC700" stroke-width="1.53846"/>
                    <!-- Dim witte buitenring -->
                    <circle opacity="0.2" cx="52.2769" cy="62.5" r="32" stroke="white"/>
                    <!-- Gele voortgangsring: vult van 0% → 100% naarmate heroProgress toeneemt -->
                    <circle
                        cx="52.2769"
                        cy="62.5"
                        r="32"
                        stroke="#FFC700"
                        stroke-width="1"
                        fill="none"
                        :stroke-dasharray="201.062"
                        :stroke-dashoffset="201.062 * (1 - heroProgress)"
                        transform="rotate(-90 52.2769 62.5)"
                    />
                    <defs>
                        <filter id="filter_hero_scroll" x="3.05176e-05" y="17.6395" width="104.554" height="108.674" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                            <feOffset dy="11.5365"/>
                            <feGaussianBlur stdDeviation="9.88841"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.05 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_scroll"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_scroll" result="shape"/>
                        </filter>
                    </defs>
                </svg>
            </button>

            <!-- Meet the team pill -->
            <a href="#team" class="absolute transition-none" :style="{ right: rightPx(1212, 387), bottom: sy(11), ...meetTeamStyle }">
                <div
                    class="flex items-center gap-4 rounded-[73px] px-4"
                    style="
                        background: rgba(0,0,0,0.88);
                        height: 73px;
                        width: 387px;
                        backdrop-filter: blur(6px);
                        box-shadow: inset 0px -2.4px 4.9px 0px rgba(0,0,0,0.1);
                    "
                >
                    <!-- Avatar circles: CMS-foto's/video's of Figma design avatars als fallback -->
                    <div class="flex -space-x-2">
                        <template
                            v-for="(avatar, i) in (teamAvatars && teamAvatars.length > 0 ? teamAvatars.slice(0, 4) : HERO_MEET_AVATARS)"
                            :key="i"
                        >
                            <video
                                v-if="isVideo(avatar)"
                                :src="avatar"
                                autoplay loop muted playsinline
                                class="rounded-full border-2 border-black object-cover"
                                style="width: 35.926px; height: 35.926px; flex-shrink: 0"
                            />
                            <img
                                v-else
                                :src="avatar"
                                alt=""
                                class="rounded-full border-2 border-black object-cover"
                                style="width: 35.926px; height: 35.926px; flex-shrink: 0"
                            />
                        </template>
                    </div>
                    <span
                        class="flex-1 text-white"
                        style="font-family: 'Avenir', sans-serif; font-size: 18px; letter-spacing: -0.18px;"
                    >
                        Meet the team
                    </span>
                    <div
                        class="flex h-[43px] w-[57px] flex-shrink-0 items-center justify-center rounded-[69px]"
                        style="background: #ffc700"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="transform: rotate(-135deg)">
                            <path d="M3 8H13M13 8L8 3M13 8L8 13" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════
         GELE INTRO SECTIE
    ════════════════════════════════════════════════ -->
    <section
        id="about"
        ref="aboutRef"
        class="bg-[#ffc700]"
        :style="{
            position: 'relative',
            zIndex: 5,
            minHeight: sy(601),
        }"
    >
        <p
            ref="aboutTextRef"
            class="absolute left-1/2 -translate-x-1/2 text-center text-[#101010]"
            :style="{
                top: sy(194),
                width: `min(${sx(832)}, calc(100vw - 64px))`,
                fontFamily: '\'Avenir\', system-ui, sans-serif',
                fontSize: '50px',
                fontWeight: '400',
                lineHeight: '60px',
                letterSpacing: '-1.5px',
            }"
        >
            {{ servicesIntroText }}
        </p>
    </section>

    <!-- ═══════════════════════════════════════════════
         SERVICES — 500vh scroll tracking
         Alle visuele content (video, gele bg, tekst) is fixed — geen sticky nodig.
    ════════════════════════════════════════════════ -->
    <section id="services" ref="servicesRef" style="height: 500vh" />

    <!-- ═══════════════════════════════════════════════
         CASES — 300vh horizontaal scroll
    ════════════════════════════════════════════════ -->
    <section id="cases" ref="casesRef" style="height: 100vh; margin-top: -80vh; position: relative; z-index: 8; background: #0c0c0c">
        <div :style="casesStickyStyle" class="relative h-full overflow-hidden bg-[#0c0c0c]">

            <!-- "Cases ↙" titel rechtsboven -->
            <div class="absolute top-6 right-[59px] z-10 flex items-center gap-3">
                <h2
                    style="
                        font-family: 'Avenir', sans-serif;
                        font-weight: 700;
                        font-style: oblique;
                        font-size: 80px;
                        line-height: 71px;
                        letter-spacing: -2.4px;
                        color: white;
                    "
                >
                    Cases
                </h2>
                <img :src="CASES_ARROW_URL" alt="" class="h-[52px] w-[52px]" />
            </div>

            <!-- Auto-scroll + draggable carousel -->
            <div
                class="absolute overflow-hidden"
                style="top: 88px; bottom: 112px; left: 0; right: 0; display: flex; align-items: center;"
                @pointerdown="onCarouselPointerDown"
                @pointermove="onCarouselPointerMove"
                @pointerup="onCarouselPointerUp"
                @pointercancel="onCarouselPointerUp"
            >
                <div
                    class="flex items-center shrink-0"
                    :style="[casesTrackStyle, { gap: CARD_GAP + 'px', paddingLeft: '59px' }]"
                >
                    <a
                        v-for="(caseItem, i) in casesDisplayItems"
                        :key="i"
                        :href="`/cases/${caseItem.slug}`"
                        class="relative shrink-0 overflow-hidden rounded-[20px] cursor-pointer select-none"
                        :style="{
                            width: caseCardSize(i).width + 'px',
                            height: caseCardSize(i).height + 'px',
                            filter: hoveredCaseIndex !== null && !isCaseHovered(i) ? 'blur(17px)' : 'none',
                            transition: 'filter 0.35s ease',
                        }"
                        @mouseenter="!carouselDragging && onCaseMouseEnter(i)"
                        @mouseleave="onCaseMouseLeave(i)"
                    >
                        <!-- Laag 1: foto achtergrond -->
                        <img
                            v-if="caseItem.photo"
                            :src="caseItem.photo"
                            :alt="caseItem.name"
                            class="absolute inset-0 h-full w-full object-cover"
                            draggable="false"
                        />
                        <div v-else class="absolute inset-0 bg-neutral-800" />

                        <!-- Laag 2: video (speelt af op hover) -->
                        <video
                            v-if="caseItem.video"
                            :ref="(el) => setCaseVideoRef(el as HTMLVideoElement | null, i)"
                            :src="caseItem.video"
                            loop muted playsinline
                            class="absolute inset-0 h-full w-full object-cover transition-opacity duration-300"
                            :class="isCaseHovered(i) ? 'opacity-100' : 'opacity-0'"
                        />

                        <!-- Laag 3: overlay — titel + pijl (altijd boven video) -->
                        <div class="absolute inset-0 z-10">
                            <!-- Donkere scrim op hover -->
                            <div
                                class="absolute inset-0 bg-black transition-opacity duration-300"
                                :class="isCaseHovered(i) ? 'opacity-40' : 'opacity-0'"
                            />

                            <!-- Standaard naam: linksboven, klein -->
                            <div
                                class="absolute top-5 left-5 transition-opacity duration-300"
                                :style="{ opacity: isCaseHovered(i) ? 0 : 1 }"
                            >
                                <span style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: 26px; line-height: 1; letter-spacing: -0.8px; color: #ffc700;">
                                    {{ caseItem.name }}
                                </span>
                            </div>

                            <!-- Hover naam: verticaal gecentreerd, groot -->
                            <div
                                class="absolute inset-0 flex items-center justify-center px-8 transition-opacity duration-300"
                                :style="{ opacity: isCaseHovered(i) ? 1 : 0 }"
                            >
                                <span style="font-family: 'Avenir', sans-serif; font-weight: 900; font-style: oblique; font-size: 80px; line-height: 1; letter-spacing: -2.4px; color: #ffc700; text-align: center;">
                                    {{ caseItem.name }}
                                </span>
                            </div>

                            <!-- Pijl rechtsonder op hover -->
                            <div
                                class="absolute bottom-5 right-5 transition-opacity duration-300"
                                :style="{ opacity: isCaseHovered(i) ? 1 : 0 }"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="58" height="56" viewBox="0 0 58 56" fill="none" style="width: 52px; height: 52px;">
                                    <path d="M21.3457 53.3944L54.8379 52.672L54.0832 17.6802" stroke="#FFC700" stroke-width="5"/>
                                    <path d="M53.8163 51.6939L1.72841 1.80544" stroke="#FFC700" stroke-width="5"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Cases footer -->
            <div class="absolute bottom-0 left-0 right-0">
                <div class="mx-[59px] border-t border-white/20" />
                <div class="flex items-end justify-between px-[59px] py-8">
                    <p
                        style="
                            font-family: 'Avenir', sans-serif;
                            font-weight: 400;
                            font-size: 28px;
                            line-height: 1.2;
                            letter-spacing: -0.56px;
                            color: white;
                        "
                    >
                        Discover more credible creativity
                    </p>
                    <a
                        href="#cases"
                        class="flex items-center gap-2 text-white"
                        style="font-family: 'Avenir', sans-serif; font-size: 18px; letter-spacing: -0.18px;"
                    >
                        Show all cases
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="transform: rotate(-135deg)">
                            <path d="M3 8H13M13 8L8 3M13 8L8 13" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- "Tell us about your project" CTA pill -->
            <div
                class="absolute right-[59px] bottom-[120px]"
            >
                <a
                    href="#contact"
                    class="flex items-center gap-4 rounded-[73px] px-4"
                    style="
                        background: rgba(0,0,0,0.7);
                        height: 73px;
                        width: 387px;
                        border: 0.6px solid rgba(255,255,255,0.05);
                        backdrop-filter: blur(6px);
                    "
                >
                    <span
                        class="flex-1 text-white"
                        style="font-family: 'Avenir', sans-serif; font-size: 18px; letter-spacing: -0.18px;"
                    >
                        Tell us about your project
                    </span>
                    <div
                        class="flex h-[43px] w-[57px] flex-shrink-0 items-center justify-center rounded-[69px]"
                        style="background: #ffc700"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="transform: rotate(-135deg)">
                            <path d="M3 8H13M13 8L8 3M13 8L8 13" stroke="black" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════
         TESTIMONIAL — 250vh
    ════════════════════════════════════════════════ -->
    <section ref="testimonialRef" style="min-height: 50vh; position: relative; z-index: 9; background: #0c0c0c">
        <div class="relative flex min-h-[50vh] flex-col items-center bg-[#0c0c0c] px-8 pt-[72px] pb-[52px]">

            <!-- Quote + attributie -->
            <div
                class="z-10 flex w-full flex-col items-center"
                :style="testimonialQuoteStyle"
            >
                <blockquote
                    class="mb-10 max-w-[477px] text-center text-white"
                    style="
                        font-family: 'Avenir', sans-serif;
                        font-weight: 400;
                        font-size: 24px;
                        line-height: 38px;
                        letter-spacing: -0.24px;
                    "
                >
                    {{ testimonialQuote }}
                </blockquote>

                <!-- Attributie: logo + avatar + naam -->
                <div class="flex items-center gap-4">
                    <img
                        v-if="testimonialLogo"
                        :src="testimonialLogo"
                        alt=""
                        class="h-8 w-auto object-contain brightness-0 invert"
                    />
                    <img
                        v-if="testimonialAuthorAvatar"
                        :src="testimonialAuthorAvatar"
                        alt=""
                        class="size-[38px] rounded-full object-cover"
                    />
                    <span
                        v-if="testimonialAuthor"
                        class="text-white"
                        style="font-family: 'Avenir', sans-serif; font-size: 18px; letter-spacing: -0.18px;"
                    >
                        {{ testimonialAuthor }}
                    </span>
                </div>

                <div
                    class="mt-2 w-full overflow-hidden"
                    :style="[clientLogosStyle, { height: `${testimonialLogoRowHeight}px` }]"
                >
                    <div
                        class="flex items-center"
                        :style="testimonialLogoTrackStyle"
                    >
                        <div
                            v-for="(logo, i) in testimonialLogoItems"
                            :key="`${logo}-${i}`"
                            class="flex shrink-0 items-center justify-center"
                            :style="{ width: `${testimonialLogoSize}px`, height: `${testimonialLogoSize}px` }"
                        >
                            <img
                                :src="logo"
                                alt=""
                                class="max-h-full max-w-full object-contain"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 z-10 h-8 w-full max-w-[calc(100vw-118px)]">
                <div class="ml-auto h-8 w-[71px]">
                    <div class="pointer-events-none flex h-8 w-[71px] items-center justify-center opacity-90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="71" height="24" viewBox="0 0 71 24" fill="none">
                            <path d="M62.125 19.3467L69.7044 11.7673L61.7856 3.84849" stroke="#FFC700" stroke-width="1.59984"/>
                            <path d="M69.2497 11.7649H46.1721" stroke="#FFC700" stroke-width="1.59984"/>
                            <g opacity="0.5">
                                <path d="M8.71094 4.4834L1.13153 12.0628L9.05031 19.9816" stroke="white" stroke-width="1.59984"/>
                                <path d="M1.58424 12.0613L24.6618 12.0613" stroke="white" stroke-width="1.59984"/>
                            </g>
                        </svg>
                    </div>
                    <button
                        class="absolute inset-y-0 left-0 w-[28px]"
                        aria-label="Vorige logo's"
                        type="button"
                        @click="shiftTestimonialLogos('prev')"
                    />
                    <button
                        class="absolute inset-y-0 right-0 w-[28px]"
                        aria-label="Volgende logo's"
                        type="button"
                        @click="shiftTestimonialLogos('next')"
                    />
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════
         TEAM — 350vh
    ════════════════════════════════════════════════ -->
    <section id="team" ref="teamRef" style="height: calc(120vh + 200px); position: relative; z-index: 10; background: #ffc700">
        <div class="sticky top-0 min-h-screen bg-[#ffc700]">
            <div class="relative flex min-h-screen w-full flex-col overflow-visible pb-[220px]">

            <!-- "Team Lemon" achtergrondtekst — exits upward faster than photos -->
            <div class="relative z-10 flex min-h-[720px] w-full items-center justify-center">
                <div
                    class="pointer-events-none flex flex-col items-center justify-center"
                    style="user-select: none"
                    :style="teamTextExitStyle"
                >
                    <p
                        class="text-center text-black"
                        style="
                            font-family: 'Avenir', sans-serif;
                            font-weight: 900;
                            font-style: oblique;
                            font-size: clamp(160px, 21vw, 339px);
                            line-height: 0.9;
                            letter-spacing: -0.04em;
                        "
                    >
                        Team<br />Lemon
                    </p>
                </div>
            </div>

            <!-- Zwevende polaroid-foto's (5 stuks) — each exits at its own speed -->
            <template v-for="(pos, i) in TEAM_PHOTO_POSITIONS" :key="i">
                <div
                    class="absolute overflow-hidden rounded-[14px]"
                    :style="{
                        left: pos.left,
                        top: pos.top,
                        width: pos.width,
                        height: pos.height,
                        transformOrigin: 'center',
                        ...teamPhotoStyle(i),
                    }"
                >
                    <template v-if="teamPhotos && teamPhotos[i]">
                        <video
                            v-if="isVideo(teamPhotos[i])"
                            :src="teamPhotos[i]"
                            class="h-full w-full object-cover"
                            autoplay
                            loop
                            :muted="true"
                            playsinline
                        />
                        <img
                            v-else
                            :src="teamPhotos[i]"
                            alt=""
                            class="h-full w-full object-cover"
                        />
                    </template>
                    <div
                        v-else
                        class="h-full w-full"
                        :class="['bg-neutral-900', 'bg-neutral-800', 'bg-neutral-700', 'bg-neutral-900', 'bg-neutral-800'][i]"
                    />
                </div>
            </template>

            <!-- Beschrijving + knop -->
            <div class="relative z-20 mt-auto flex w-full justify-center px-8">
                <div
                    class="pointer-events-none flex flex-col items-center"
                    :style="teamContentStyle"
                >
                    <p
                        class="mb-6 max-w-[390px] text-center text-black"
                        style="
                            font-family: 'Avenir', sans-serif;
                            font-weight: 400;
                            font-size: 18px;
                            line-height: 24px;
                            letter-spacing: -0.18px;
                        "
                    >
                        {{ teamDescription }}
                    </p>
                    <a
                        :href="teamButtonHref || '#about'"
                        class="pointer-events-auto flex h-[65px] w-[155px] items-center justify-center rounded-[34px] border border-black text-black"
                        style="font-family: 'Avenir', sans-serif; font-size: 23px; letter-spacing: -0.69px;"
                    >
                        {{ teamButtonText || 'About us' }}
                    </a>
                </div>
            </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════ -->
    <SiteFooter />

</template>

<style>
html,
body {
    overflow-x: hidden;
}
</style>
