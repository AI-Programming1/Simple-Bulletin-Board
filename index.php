<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Simple Bulletin Board</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <div class="container">
      <h1>📌 Simple Bulletin Board</h1>
      <div class="sub">Post job listings, items for sale, announcements, and more. Posts auto‑remove after 31 days.</div>
    </div>
  </header>

  <main class="container">
    <section class="card" aria-labelledby="compose">
      <form id="postForm">
        <div class="row">
          <div>
            <label for="subject">Subject <span class="tiny">(required)</span></label>
            <input id="subject" name="subject" type="text" maxlength="140" placeholder="e.g., Hiring barista • 20 hrs/week" required />
          </div>
          <div>
            <label for="category">Category</label>
            <select id="category" name="category">
              <option>General</option>
              <option>Job</option>
              <option>For Sale</option>
              <option>Event</option>
              <option>Lost & Found</option>
              <option>Service</option>
              <option>Announcement</option>
            </select>
          </div>
        </div>
        <div>
          <label for="details">Details <span class="tiny">(optional)</span></label>
          <textarea id="details" name="details" maxlength="2000" placeholder="Add description, contact info, price, dates…"></textarea>
        </div>
        <div class="actions">
          <button class="btn" id="postBtn" type="submit">Post</button>
          <button class="btn-secondary" id="clearBtn" type="button">Clear</button>
        </div>
      </form>
    </section>

    <section style="margin-top:18px">
      <div class="meta" style="margin-bottom:8px">
        <div class="pill">Active Posts</div>
        <span class="right tiny" id="lastRefreshed"></span>
      </div>
      <div id="posts" class="list"></div>
    </section>
  </main>

  <div class="toast" id="toast" role="status" aria-live="polite"></div>

  <script src="script.js"></script>
</body>
</html>