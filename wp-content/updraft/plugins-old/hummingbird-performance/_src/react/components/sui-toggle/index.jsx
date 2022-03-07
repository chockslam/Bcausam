/**
 * External dependencies
 */
import React from 'react';

/**
 * Toggle functional component.
 *
 * @param {string}  text      Toggle text.
 * @param {string}  id        Toggle ID.
 * @param {string}  name      Toggle name.
 * @param {Object}  onChange  On change action.
 * @param {boolean} checked   Checked status.
 * @return {*} Toggle component.
 * @class
 */
export default function Toggle( {
	text,
	id,
	name,
	onChange,
	checked = false,
	...props
} ) {
	return (
		<div className="sui-form-field">
			<label htmlFor={ id } className="sui-toggle">
				<input
					type="checkbox"
					name={ name }
					id={ id }
					checked={ checked }
					onChange={ onChange }
					aria-labelledby={ id + '-label' }
					{ ...props }
				/>
				<span className="sui-toggle-slider" aria-hidden="true" />
				{ text && (
					<span id={ id + '-label' } className="sui-toggle-label">
						{ text }
					</span>
				) }
			</label>
		</div>
	);
}
