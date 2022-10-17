function toggleDealerResourceComplete( parent ) {
  const linkComplete = parent.querySelector(".link-completion.complete");
  const linkIncomplete = parent.querySelector(".link-completion.incomplete");
  
  linkIncomplete.classList.add("hidden");
  linkComplete.classList.remove("hidden");
}