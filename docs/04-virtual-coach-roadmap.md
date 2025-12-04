Virtual Coach – Roadmap & Phases (TVS Virtual Sports)
=====================================================

Context
-------
This document outlines the phased implementation of a “Virtual Coach” feature
for the TVS Virtual Sports platform (WordPress + tvs-virtual-sports plugin + React app).

The goal is to provide lightweight, near real-time feedback during virtual runs/rides/walks,
based on current effort and previous performance – with distinct “coach styles”:

- Military Bootcamp style
- Encouraging but realistic
- Supporting parent

The roadmap is split into three levels:

- Level 1 – Rule-based coach (no external AI)
- Level 2 – Hybrid: rule-based evaluation + optional LLM text generation
- Level 3 – Conversational AI coach (text-based), opt-in and resource-aware

The core of the system is the **server-side PHP coach engine** (`TVS_Coach_Engine`),
which all levels depend on.

Key constraints
----------------
- **Low resource usage**: minimal CPU/JS overhead, minimal network traffic.
- **Near real-time feeling**: feedback every X seconds or at route milestones.
- **Graceful degradation**: if any AI/LLM integration fails or is disabled,
  the coach must fall back to deterministic rule-based messages.
- **Privacy & future-proofing**: coach logic should be encapsulated so we
  can later swap in different data sources (treadmill, Strava, wearables).

Data sources
------------
- Route/Session context from TVS:
  - `routeId`
  - `elapsed_s` (time in seconds since start)
  - `distance_m` (estimated from video progress or live device data)
  - `pace` (min/km or similar)
  - Maybe `target_pace` or user goal (optional for later)
- Historical stats (from tvs_activity + Strava integration later):
  - Best time on this route
  - Typical pace (last N sessions)
  - Longest distance / toughest session
- User preferences:
  - Selected `coach_style`: `bootcamp`, `realistic`, `parent`
  - Optionally: “focus of this session” (finish vs. push vs. recovery).

Phase overview
--------------
### Engine foundation – v2.5

**Server-side Coach Engine (`TVS_Coach_Engine`)**

- PHP class that:
  - Accepts a compact metrics payload (`route_id`, `elapsed_s`, `distance_m`, `pace`).
  - Computes a `status` object:
    - phase (early/mid/late)
    - has_history (bool)
    - delta_vs_average (ahead/behind/on_track/no_history)
    - effort_hint (easy/steady/hard).
  - Renders a **rule-based message** from the status + coach style.
  - Exposes a placeholder for future AI-based rendering.

All later work (REST, React, AI, chat) depends on this engine.

---

### v2.5 – Level 1

**Level 1: Rule-based coach (MVP)** built on `TVS_Coach_Engine`:

- REST endpoint `/tvs/v1/coach` receives a compact snapshot
  of the current session metrics.
- The endpoint:
  - Uses `TVS_Coach_Engine::evaluate_status()` to compute status.
  - Uses `render_message_rule_based()` to select a short message.
  - Returns a small JSON payload: `{ message, severity, suggested_next_check_s }`.
- The React app:
  - Calls this endpoint periodically (e.g., every 45–60 seconds).
  - Shows a small overlay and optionally uses Web Speech (TTS) if enabled.

No AI is used in Level 1.

---

### v2.5 – Level 2

**Level 2: Hybrid / LLM-ready coach**, extending `TVS_Coach_Engine`:

- Status evaluation stays in PHP, in the engine.
- `render_message_ai()` may call an external LLM/AI service to generate
  the final text message from the compact status + persona.
- There is a strict rate limit and hard timeouts:
  - If a response takes too long or fails, the engine falls back to rule-based text.
- AI usage is controlled via settings:
  - “Enable AI text generation for coach messages”.

`/tvs/v1/coach` continues to use the same JSON response shape and simply decides, per request,
whether to serve an AI-generated or rule-based message.

---

### v2.6+ – Level 3

**Level 3: Conversational AI coach (text-based)**

- Users can “talk” to the coach via a chat-like UI during or after sessions.
- The coach:
  - Has access to summarized history (not raw logs).
  - Uses the same coach-style personas but in longer-form, reflective feedback.
- Key goals:
  - Keep prompts short and structured.
  - Cache context between interactions to minimize token use.
  - Remain optional, disabled by default for performance and cost control.

This level likely introduces:

- `TVS_Coach_Conversation` helper class for:
  - Conversations per user/session.
  - Short, rolling summaries.
  - Prompt construction that reuses persona and stat helpers from the engine.
- New endpoints such as:
  - `POST /tvs/v1/coach/chat`
  - Optional `GET /tvs/v1/coach/chat-history`.

---

Architecture sketch
-------------------
- WordPress / Plugin (tvs-virtual-sports)
  - Core engine: `TVS_Coach_Engine` (status + rule-based + AI hook).
  - Optional conversation layer: `TVS_Coach_Conversation`.
  - New REST endpoints:
    - `/tvs/v1/coach` (Level 1 & 2)
    - `/tvs/v1/coach/chat` (Level 3).
- Frontend (React app)
  - `useCoach()` hook for periodic feedback.
  - `<CoachOverlay />` for inline messages.
  - Optional chat UI surface for Level 3.

Milestone focus
---------------
- **Milestone v2.5 (Virtual Coach MVP – Engine + Levels 1–2):**
  - Implement `TVS_Coach_Engine`.
  - Implement `/tvs/v1/coach` + React integration.
  - Add AI/LLM-ready abstraction in the engine:
    - `render_message_ai()` with strict timeouts and rate limits.
  - Keep AI optional and fully backward compatible with rule-based behavior.

- **Milestone v2.6+ (Level 3 – Conversational AI):**
  - Design coach persona prompts and constraints.
  - Add separate “Coach Chat” surface in UI.
  - Implement strict rate limiting, caching and fallbacks for conversations.
