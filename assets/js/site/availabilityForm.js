/*
Set up interactive features of the availability form.
*/
const formName = document.querySelector('[name="user_full_availability"]')
  ? 'user_full_availability'
  : 'user_partial_availability'

const availability = document.querySelector(`[name="${formName}"]`)

if (availability) {
  const willingToReview = availability.querySelector(`[name="${formName}[willingToReview]"]`)

  const willingToComment = availability.querySelector(`[name="${formName}[willingToComment]"]`)

  const keywords = availability.querySelector(`[name="${formName}[keywords]"]`)

  const fixKeywordsRequired = () => {
    if (willingToReview.checked || (willingToComment && willingToComment.checked)) {
      keywords.setAttribute('required', 'required')
    } else {
      keywords.removeAttribute('required')
    }
  }

  willingToReview.addEventListener('change', fixKeywordsRequired)

  if (willingToComment) willingToComment.addEventListener('change', fixKeywordsRequired)

  fixKeywordsRequired()
}
