
# GNDEC Training Letter Moodle local plugin
Target Moodle 4.5.x. Copy this folder to `moodle/local/training_letter`, then visit Site administration > Notifications.
Pages: `index.php`, `request.php`, `view.php`, `manage.php`, `letter.php?id=ID`.
Grant `local/training_letter:manage` to the coordinator role at system context. Managers have it by default.
The generated letter is an HTML draft printable to PDF, not an official signed letter. Verify its wording/layout against the college's official template before real use. Test with dummy data first.

The PDF generator selects only the supplied, verified GNDEC formats:

- B.Tech six-week industrial training
- B.Tech six-month industrial training
- B.Tech software training
- MBA 6-8 week summer training
- MCA six-month industrial training

Course, training type, duration, and semester are stored with each request. Combinations
without a supplied official format are rejected instead of receiving invented wording.