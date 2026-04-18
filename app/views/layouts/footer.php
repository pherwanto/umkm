</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('click', function(e){ if(e.target.matches('.btn-remove-row')){ e.preventDefault(); const row=e.target.closest('.item-row'); if(row) row.remove(); } });
</script>
</body></html>
