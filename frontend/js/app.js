const API = {
  categories: "../API/categories/index.php",
  products: "../API/products/index.php",
  users: "../API/users/index.php",
};
const state = {
  resource: "products",
  rows: [],
  categories: [],
  editingId: null,
  toastTimer: null,
};
const $ = (s) => document.querySelector(s);
const resourceMeta = {
  products: {
    title: "Products",
    singular: "product",
    count: "Total products",
    directory: "Product directory",
    subtitle: "Review and manage your product records.",
    columns: ["Product", "Category", "Price", "Quantity", "Created", "Actions"],
  },
  categories: {
    title: "Categories",
    singular: "category",
    count: "Total categories",
    directory: "Category directory",
    subtitle: "Organize the products in your store.",
    columns: ["Category", "Category ID", "Actions"],
  },
  users: {
    title: "Users",
    singular: "user",
    count: "Total users",
    directory: "User directory",
    subtitle: "Manage user profiles and account details.",
    columns: ["User", "Username", "Email", "Actions"],
  },
};
async function request(url, options = {}) {
  const response = await fetch(url, {
    ...options,
    headers: {
      ...(options.body ? { "Content-Type": "application/json" } : {}),
      ...(options.headers || {}),
    },
  });
  const raw = await response.text();
  let payload;
  try {
    payload = raw ? JSON.parse(raw) : {};
  } catch {
    throw new Error(
      `The server returned a non-JSON response (HTTP ${response.status}). Check the PHP path and Apache error log.`,
    );
  }
  if (!response.ok || payload.success === false)
    throw new Error(
      payload.message || `Request failed with HTTP ${response.status}.`,
    );
  return payload;
}
function escapeHtml(value) {
  return String(value ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
}
function money(v) {
  return new Intl.NumberFormat("en-JO", {
    style: "currency",
    currency: "JOD",
    minimumFractionDigits: 2,
  }).format(Number(v || 0));
}
function dateOnly(v) {
  if (!v) return "—";
  const d = new Date(String(v).replace(" ", "T"));
  return Number.isNaN(d.getTime())
    ? String(v)
    : d.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
}
function showToast(message, error = false) {
  const t = $("#toast");
  t.textContent = message;
  t.classList.toggle("error", error);
  t.classList.add("show");
  clearTimeout(state.toastTimer);
  state.toastTimer = setTimeout(() => t.classList.remove("show"), 3300);
}
function setBusy(busy) {
  $("#apiStatus").textContent = busy ? "Working…" : "Ready";
}
async function loadResource() {
  setBusy(true);
  $("#tableBody").innerHTML =
    '<tr><td class="empty" colspan="8">Loading records…</td></tr>';
  try {
    if (state.resource === "products")
      state.categories = (await request(API.categories)).data;
    state.rows = (await request(API[state.resource])).data || [];
    $("#apiStatus").textContent = "Online";
    renderTable();
  } catch (e) {
    state.rows = [];
    renderTable();
    $("#apiStatus").textContent = "Error";
    showToast(e.message, true);
  } finally {
    setBusy(false);
  }
}
function switchResource(resource) {
  state.resource = resource;
  const meta = resourceMeta[resource];
  document
    .querySelectorAll(".nav-item")
    .forEach((b) =>
      b.classList.toggle("active", b.dataset.resource === resource),
    );
  $("#pageTitle").textContent = meta.title;
  $("#crumb").textContent = meta.title.toUpperCase();
  $("#countLabel").textContent = meta.count;
  $("#listTitle").textContent = meta.directory;
  $("#listSubtitle").textContent = meta.subtitle;
  $("#createBtn").textContent = `＋ Add ${meta.singular}`;
  $("#searchInput").value = "";
  loadResource();
}
function visibleRows() {
  const q = $("#searchInput").value.trim().toLowerCase();
  if (!q) return state.rows;
  return state.rows.filter((row) =>
    Object.values(row).some((v) =>
      String(v ?? "")
        .toLowerCase()
        .includes(q),
    ),
  );
}
function renderTable() {
  const meta = resourceMeta[state.resource];
  $("#totalCount").textContent = state.rows.length;
  $("#resultCount").textContent =
    `${visibleRows().length} record${visibleRows().length === 1 ? "" : "s"}`;
  $("#tableHead").innerHTML =
    `<tr>${meta.columns.map((c) => `<th>${escapeHtml(c)}</th>`).join("")}</tr>`;
  const rows = visibleRows();
  if (!rows.length) {
    $("#tableBody").innerHTML =
      `<tr><td class="empty" colspan="${meta.columns.length}">${state.rows.length ? "No matching records." : "No records yet. Add your first " + meta.singular + "."}</td></tr>`;
    $("#footerText").textContent = "Nothing to display";
    return;
  }
  $("#tableBody").innerHTML = rows
    .map((row) => {
      let cells = "";
      if (state.resource === "categories")
        cells = `<td><div class="record-title">${escapeHtml(row.CategorieName)}</div></td><td>#${escapeHtml(row.CategorieID)}</td>`;
      if (state.resource === "users")
        cells = `<td><div class="record-title">${escapeHtml(row.fullName)}</div><div class="record-sub">ID #${escapeHtml(row.id)}</div></td><td>${escapeHtml(row.username)}</td><td>${escapeHtml(row.email)}</td>`;
      if (state.resource === "products")
        cells = `<td><div class="record-title">${escapeHtml(row.ProductName)}</div><div class="record-sub">ID #${escapeHtml(row.id)}</div></td><td><span class="pill">${escapeHtml(row.CategorieName || "Uncategorized")}</span></td><td>${money(row.Price)}</td><td><span class="pill ${Number(row.Quantity) < 5 ? "qty-low" : ""}">${escapeHtml(row.Quantity)} in stock</span></td><td>${escapeHtml(dateOnly(row.CreatedDate))}</td>`;
      const id = state.resource === "categories" ? row.CategorieID : row.id;
      return `<tr>${cells}<td><div class="row-actions"><button class="action-btn" data-edit="${escapeHtml(id)}">Edit</button><button class="action-btn delete" data-delete="${escapeHtml(id)}">Delete</button></div></td></tr>`;
    })
    .join("");
  $("#footerText").textContent =
    `Showing ${rows.length} of ${state.rows.length} records`;
}
function field(name, label, value = "", type = "text", opts = {}) {
  const full = opts.full ? " full" : "";
  let control = "";
  const id = `field-${name}`;
  if (opts.options)
    control = `<select id="${id}" name="${name}" ${opts.required === false ? "" : "required"}><option value="">Choose ${escapeHtml(label.toLowerCase())}</option>${opts.options.map((o) => `<option value="${escapeHtml(o.value)}" ${String(o.value) === String(value) ? "selected" : ""}>${escapeHtml(o.label)}</option>`).join("")}</select>`;
  else if (type === "textarea")
    control = `<textarea id="${id}" name="${name}" placeholder="${escapeHtml(opts.placeholder || "")}" ${opts.required ? "required" : ""}>${escapeHtml(value)}</textarea>`;
  else
    control = `<input id="${id}" name="${name}" type="${type}" value="${escapeHtml(value)}" ${opts.required ? "required" : ""} ${opts.min !== undefined ? `min="${opts.min}"` : ""} ${opts.step ? `step="${opts.step}"` : ""} placeholder="${escapeHtml(opts.placeholder || "")}">`;
  return `<div class="field${full}"><label for="${id}">${escapeHtml(label)}${opts.required ? " *" : ""}</label>${control}${opts.hint ? `<span class="hint">${escapeHtml(opts.hint)}</span>` : ""}</div>`;
}
function openForm(id = null) {
  state.editingId = id === null ? null : Number(id);
  const row =
    id === null
      ? {}
      : state.rows.find(
          (r) =>
            Number(state.resource === "categories" ? r.CategorieID : r.id) ===
            Number(id),
        );
  if (id !== null && !row) {
    showToast(
      "Record not found in the current list. Refresh and try again.",
      true,
    );
    return;
  }
  const edit = state.editingId !== null;
  const resource = state.resource;
  let fields = "";
  if (resource === "categories")
    fields = field("name", "Category name", row?.CategorieName || "", "text", {
      required: true,
      full: true,
      placeholder: "e.g. Home accessories",
    });
  if (resource === "users")
    fields =
      field("fullName", "Full name", row?.fullName || "", "text", {
        required: true,
      }) +
      field("username", "Username", row?.username || "", "text", {
        required: true,
      }) +
      field("email", "Email", row?.email || "", "email", {
        required: true,
        full: true,
      }) +
      field(
        "password",
        edit ? "New password (optional)" : "Password",
        "",
        "password",
        {
          required: !edit,
          full: true,
          hint: edit
            ? "Leave blank to keep the current password. Minimum 8 characters."
            : "Use at least 8 characters.",
        },
      );
  if (resource === "products")
    fields =
      field("ProductName", "Product name", row?.ProductName || "", "text", {
        required: true,
        full: true,
      }) +
      field("CategorieID", "Category", row?.CategorieID ?? "", "text", {
        options: state.categories.map((c) => ({
          value: c.CategorieID,
          label: c.CategorieName,
        })),
        required: true,
      }) +
      field("Price", "Price (JOD)", row?.Price ?? "", "number", {
        required: true,
        min: 0,
        step: "0.01",
      }) +
      field("Quantity", "Quantity", row?.Quantity ?? "", "number", {
        required: true,
        min: 0,
        step: "1",
      }) +
      field("PhotoURL", "Photo URL", row?.PhotoURL || "", "url", {
        full: true,
        placeholder: "https://…",
      }) +
      field("Description", "Description", row?.Description || "", "textarea", {
        full: true,
      });
  $("#formFields").innerHTML = fields;
  $("#formError").textContent = "";
  $("#modalEyebrow").textContent = edit ? "UPDATE RECORD" : "NEW RECORD";
  $("#modalTitle").textContent =
    `${edit ? "Edit" : "Add"} ${resourceMeta[resource].singular}`;
  $("#saveBtn").textContent = edit
    ? "Save changes"
    : `Create ${resourceMeta[resource].singular}`;
  $("#modal").classList.remove("hidden");
  document.body.style.overflow = "hidden";
  setTimeout(() => $("#formFields input, #formFields select")?.focus(), 50);
}
function closeForm() {
  $("#modal").classList.add("hidden");
  document.body.style.overflow = "";
  state.editingId = null;
}
async function saveRecord(event) {
  event.preventDefault();
  const form = new FormData(event.currentTarget);
  const data = Object.fromEntries(form.entries());
  $("#formError").textContent = "";
  if (state.resource === "products") {
    data.CategorieID = Number(data.CategorieID);
    data.Price = Number(data.Price);
    data.Quantity = Number(data.Quantity);
    data.Description = data.Description || null;
    data.PhotoURL = data.PhotoURL || null;
  }
  if (state.resource === "users" && state.editingId !== null && !data.password)
    delete data.password;
  const url =
    state.editingId === null
      ? API[state.resource]
      : `${API[state.resource].replace(/index\.php$/, "item.php")}?id=${encodeURIComponent(state.editingId)}`;
  const method = state.editingId === null ? "POST" : "PUT";
  $("#saveBtn").disabled = true;
  $("#saveBtn").textContent = "Saving…";
  try {
    const result = await request(url, { method, body: JSON.stringify(data) });
    closeForm();
    showToast(result.message || "Changes saved successfully.");
    await loadResource();
  } catch (e) {
    $("#formError").textContent = e.message;
  } finally {
    $("#saveBtn").disabled = false;
    $("#saveBtn").textContent =
      state.editingId === null ? "Create record" : "Save changes";
  }
}
async function deleteRecord(id) {
  const row = state.rows.find(
    (r) =>
      Number(state.resource === "categories" ? r.CategorieID : r.id) ===
      Number(id),
  );
  const label =
    state.resource === "categories"
      ? row?.CategorieName
      : state.resource === "users"
        ? row?.username
        : row?.ProductName;
  if (
    !confirm(
      `Delete “${label || "this record"}”? This action cannot be undone.`,
    )
  )
    return;
  try {
    const url = `${API[state.resource].replace(/index\.php$/, "item.php")}?id=${encodeURIComponent(id)}`;
    const result = await request(url, { method: "DELETE" });
    showToast(result.message || "Record deleted.");
    await loadResource();
  } catch (e) {
    showToast(e.message, true);
  }
}
document
  .querySelectorAll(".nav-item")
  .forEach((b) =>
    b.addEventListener("click", () => switchResource(b.dataset.resource)),
  );
$("#createBtn").addEventListener("click", () => openForm());
$("#refreshBtn").addEventListener("click", loadResource);
$("#searchInput").addEventListener("input", renderTable);
$("#recordForm").addEventListener("submit", saveRecord);
$("#closeModal").addEventListener("click", closeForm);
$("#cancelBtn").addEventListener("click", closeForm);
$("#modal").addEventListener("click", (e) => {
  if (e.target.dataset.close) closeForm();
});
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !$("#modal").classList.contains("hidden"))
    closeForm();
});
$("#tableBody").addEventListener("click", (e) => {
  const edit = e.target.closest("[data-edit]");
  const del = e.target.closest("[data-delete]");
  if (edit) openForm(edit.dataset.edit);
  if (del) deleteRecord(del.dataset.delete);
});
switchResource("products");
