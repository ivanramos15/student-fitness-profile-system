let table,
  rows,
  originalRows,
  currentPage,
  rowsPerPage,
  sortDirection,
  currentSortKey;

function setUp() {
  table = document.getElementById("testsTable");
  rows = Array.from(table.querySelectorAll("tbody tr"));
  originalRows = [...rows];
  currentPage = 1;
  rowsPerPage = 10;
  sortDirection = 1;
  currentSortKey = "";

  renderTable();
}

function renderTable() {
  const start = (currentPage - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  rows.forEach((row, idx) => {
    row.style.display = idx >= start && idx < end ? "" : "none";
  });
  document.getElementById("prevBtn").disabled = currentPage === 1;
  document.getElementById("nextBtn").disabled = end >= rows.length;
}

function prevPage() {
  if (currentPage > 1) {
    currentPage--;
    renderTable();
  }
}

function nextPage() {
  if (currentPage * rowsPerPage < rows.length) {
    currentPage++;
    renderTable();
  }
}

function filterTable() {
  const filter = document.getElementById("searchBox").value.toLowerCase();
  const tbody = table.querySelector("tbody");

  tbody.innerHTML = "";

  rows = originalRows.filter((row) => {
    const text = row.textContent.toLowerCase();
    return text.includes(filter);
  });

  rows.forEach((row) => tbody.appendChild(row));

  currentPage = 1;

  if (currentSortKey) sortTable(currentSortKey, true);
  else renderTable();
}

function sortTable(key, preserveOrder = false) {
  const columnIndexes = {
    student_id: 0,
    first_name: 1,
    last_name: 2,
    section_id: 3,
    program_id: 4,
    date: 5,
  };
  const colIndex = columnIndexes[key];

  if (!preserveOrder) {
    if (currentSortKey === key) {
      sortDirection *= -1;
    } else {
      currentSortKey = key;
      sortDirection = 1;
    }
  }

  rows.sort((a, b) => {
    const textA = a.children[colIndex].textContent.trim();
    const textB = b.children[colIndex].textContent.trim();

    if (key === "date") {
      const dateA = new Date(textA === "N/A" ? 0 : textA);
      const dateB = new Date(textB === "N/A" ? 0 : textB);
      return sortDirection * (dateA - dateB);
    }

    if (!isNaN(textA) && !isNaN(textB)) {
      return sortDirection * (parseFloat(textA) - parseFloat(textB));
    }

    return sortDirection * textA.localeCompare(textB);
  });

  const tbody = table.querySelector("tbody");
  tbody.innerHTML = "";
  rows.forEach((row) => tbody.appendChild(row));

  document.querySelectorAll("th").forEach((th) => {
    th.classList.remove("sorted", "asc", "desc");
  });

  const sortedTh = table.querySelectorAll("th")[colIndex];
  sortedTh.classList.add("sorted");
  sortedTh.classList.add(sortDirection === 1 ? "asc" : "desc");

  currentPage = 1;
  renderTable();
}

function handleRowClick(row, studentID) {
  if (row.getAttribute("data-has-fitness") === "0") {
    showToast("No fitness record", "error");
  } else {
    window.location.href = "index.php?studentID=" + studentID;
  }
}
