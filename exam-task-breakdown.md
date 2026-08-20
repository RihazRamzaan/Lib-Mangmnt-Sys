# Step-by-Step Task Breakdown Per Member (4-Hour Exam)

Based on the Library Management System template. Swap entity names for your actual theme, but the steps and time allocation stay the same shape.

---

## Phase 0a — All 4 Members Together (0:00 – 0:10)

1. Read the full requirements sheet together, underline every required feature
2. Draw the DB schema on paper/whiteboard — tables, columns, foreign keys
3. Agree on the endpoint contract (file names, what fields each PHP file expects/returns — see table in the project template)
4. Agree on naming conventions (snake_case for DB columns, consistent variable names, file naming) so integration doesn't break later
5. Nominate one person as **Repo Setup Lead** for the next 10 minutes (doesn't need to be the same person as any role below — pick whoever is fastest with Git)

---

## Phase 0b — Repo Setup Lead Only (0:10 – 0:20)

**Goal: create the architecture first, so the other 3 members clone into a repo that already makes sense — nobody should have to guess folder names, file names, or conventions.**

While this happens, the other 3 members re-read the project template's UI guidelines and endpoint contract so they're ready to code the instant they clone.

| Step | Action |
|---|---|
| 1 | `git init` (or create the repo on GitHub/GitLab and clone it) |
| 2 | Create the **full folder structure** from the project template — every folder, even empty ones |
| 3 | Create **empty placeholder files** for every file in the contract (`add_book.php`, `login.php`, `style.css`, `validation.js`, etc.) — even a single comment line like `<?php // TODO: add_book logic (owner: Member A) ?>` is enough |
| 4 | Add the **project template file** (schema, contract, UI guidelines, AI-context section) into the repo root — e.g. `PROJECT_CONTEXT.md` — so it's the single shared source of truth |
| 5 | Add a short root `README.md`: one line on the project theme, a link to `PROJECT_CONTEXT.md`, and each member's assigned file ownership |
| 6 | Commit: `git commit -m "chore: project scaffold and conventions"` |
| 7 | Push, then share the repo link/invite with the other 3 immediately |

Once this is pushed, every member clones/pulls, opens the repo in their own editor, and starts their Phase 1 tasks below at the 0:20 mark.

---

## AI Assistant & Git Collaboration Practices

Since everyone's using AI tools in their own IDE, a few small habits prevent the AI assistants on 4 different machines from quietly working against each other:

- **Point your AI assistant at `PROJECT_CONTEXT.md` first.** Before asking your AI IDE tool (Copilot, Cursor, Claude Code, etc.) to generate anything, tell it to read `PROJECT_CONTEXT.md` in the repo root, or paste its contents into the chat. This gives every member's AI the same tech-stack constraints, naming conventions, folder structure, and UI style — so nobody's assistant suggests a framework, a different naming style, or a different file layout than what's agreed.
- **Require deep explanatory comments on every AI-generated block.** When prompting your AI assistant, explicitly ask it to comment its output — what each function/query does and why, not just a restatement of the code. You'll be questioned live on your own files, so a comment-free block someone else's AI wrote for you is a liability, not a shortcut.
- **Strictly no editing outside your assigned files.** This is a hard rule, not a guideline: each file belongs to exactly one member for the whole 4 hours. Don't let your AI assistant touch, "fix," or refactor a file owned by someone else, even with good intentions — this is the #1 cause of last-minute merge conflicts and lost work in a timed team exam. If you spot an issue in someone else's file, tell them; don't fix it yourself.
- **Shared files need a single owner.** `header.php`, `footer.php`, and `style.css` are touched by everyone conceptually but should be *edited* by one person only (recommend Member C) to avoid merge conflicts; others request changes rather than editing directly.
- **Commit and pull often** — every 15–20 minutes, not just at integration time. Small, frequent commits mean any conflict is a one-line fix instead of a tangle at hour 3.
- **Never let AI auto-install a package/library** to "help" (e.g. adding Bootstrap via CDN, a validation library, jQuery). If your AI assistant suggests this, decline — it breaks the no-framework constraint even if the code itself looks fine.

---

**Standing rule for every table below:** comment your code as you write it (don't leave it for later — you won't have time), and only touch the files listed as yours.

## Member A — Database & Core Backend Lead

**Goal: DB is live and core CRUD logic works before anyone else needs it.**

| Time | Step |
|---|---|
| 0:20–0:35 | Create the database and all tables from the agreed schema, run it, fix any errors |
| 0:35–0:45 | Insert 5–10 rows of sample/seed data into each table (so others can test against real data immediately) |
| 0:45–0:55 | Write `config/db.php` (connection file) and share it with the team immediately |
| 0:55–1:30 | Write `add_book.php` and `delete_book.php` using prepared statements |
| 1:30–2:00 | Write `edit_book.php` (update logic) |
| 2:00–2:30 | Help Member B build `borrow_book.php` / `return_book.php` (these depend on 2 foreign keys, so pair up here) |
| 2:30–3:00 | Write the SQL for the report page (`JOIN` + `GROUP BY`/`COUNT` queries) — hand off to Member B to wire into `report.php` |
| 3:00–3:30 | Integration: test every form on the live site against real DB, fix query bugs |
| 3:30–4:00 | Rehearse explaining schema decisions, prepared statements, and query logic |

---

## Member B — Auth & Secondary Backend

**Goal: Login/session system ready early since everything else needs it to test properly.**

| Time | Step |
|---|---|
| 0:20–0:30 | Write `register.php` (insert into `users`, use `password_hash()`) |
| 0:30–0:50 | Write `login.php` (verify with `password_verify()`, start session, store `user_id` and `role` in `$_SESSION`) |
| 0:50–1:00 | Write `logout.php` (`session_destroy()`) |
| 1:00–1:20 | Write `includes/validate.php` — shared server-side validation functions (empty field checks, email format, etc.) reused across forms |
| 1:20–2:00 | Write `list_books.php` with search/filter logic (`WHERE title LIKE ?` or category filter) |
| 2:00–2:30 | Write `borrow_book.php` / `return_book.php` logic (pair with Member A on the SQL) |
| 2:30–3:00 | Build `report.php` — plug in Member A's aggregate queries, format output |
| 3:00–3:30 | Integration testing: try registering, logging in as both roles, confirm access control works |
| 3:30–4:00 | Rehearse explaining session handling, password hashing, and role-based access |

---

## Member C — Frontend Structure & Styling

**Goal: All pages exist and look consistent, ready for JS to hook into.**

| Time | Step |
|---|---|
| 0:20–0:40 | Build `index.php` (login/landing page) and `register.php`'s HTML form |
| 0:40–1:10 | Build the "add/edit book" form HTML — use the exact field names Member A/B agreed on in the contract |
| 1:10–1:40 | Build the book listing page (table layout) with search bar and filter dropdown |
| 1:40–2:10 | Build the borrow/return page HTML |
| 2:10–2:40 | Build the report/dashboard page layout (cards or table for summary stats) |
| 2:40–3:10 | Write `css/style.css` — consistent colors, spacing, responsive layout (flexbox/grid) across all pages |
| 3:10–3:30 | Build shared `header.php` / `footer.php` (nav bar that changes based on login/role) and include them on every page |
| 3:30–4:00 | Integration pass with Member D — confirm every ID/class JS needs actually exists in the HTML; rehearse explaining CSS layout choices |

---

## Member D — Frontend Logic / JavaScript

**Goal: Every form validates properly and any dynamic behavior works smoothly.**

| Time | Step |
|---|---|
| 0:20–0:40 | List every form in the project and the validation rules each needs (required fields, number ranges, email format) |
| 0:40–1:10 | Write `validation.js` for the register/login forms (required fields, basic email regex) |
| 1:10–1:50 | Write validation for the add/edit book form (title required, quantity must be a positive number, etc.) |
| 1:50–2:20 | Add any dynamic UX behavior: confirmation dialogs before delete, disable submit button while processing, show/hide fields |
| 2:20–2:50 | If using `fetch()` for anything dynamic (e.g., live search without page reload), build and test that here — otherwise use this time for extra polish (loading states, inline error messages) |
| 2:50–3:20 | Attach validation script to every relevant page once Member C's HTML is ready |
| 3:20–3:40 | Full click-through of every form in the browser, checking that invalid input is actually blocked |
| 3:40–4:00 | Rehearse explaining why client-side validation exists alongside server-side validation, and walk through your JS logic line by line |

---

## Phase Final — All 4 Together (3:30 – 4:00, overlapping with individual wrap-up)

1. Run through the entire app end-to-end as if you were the examiner: register → login → add → search → edit → delete → borrow → return → view report
2. Fix any last-minute integration breaks (mismatched field names are the #1 culprit)
3. Each person states out loud, in one sentence, what their part does and why one key technical decision was made (this is your presentation rehearsal)
4. Make sure the DB has enough clean sample data left in it for a live demo (don't leave it in a broken half-tested state)

---

## Notes on Timing

- These blocks assume smooth progress. Build in the assumption that **Phase 0a might run long** (schema arguments are common) — if so, compress everyone's first task block, not the last one (integration and rehearsal at the end matters more than an extra 10 minutes of individual coding). Phase 0b (repo scaffold) should stay close to 10 minutes regardless — it's mechanical, not a design discussion.
- Every member's very first action after 0:20 should be: **pull the repo, confirm the folder structure and placeholder files match what you expect, point your AI assistant at `PROJECT_CONTEXT.md`** — then start coding. Skipping this is how two people end up building the same thing differently.
- Members A and B should sync every ~45 minutes since their files depend on each other (borrow/return especially).
- Don't let Member C or D sit idle waiting for backend files — they can build against dummy/hardcoded data first and swap in real PHP output once it's ready.
