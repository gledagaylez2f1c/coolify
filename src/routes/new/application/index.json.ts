import { getUserDetails, uniqueName } from '$lib/common';
import * as db from '$lib/database';
import { PrismaErrorHandler } from '$lib/database';
import type { RequestHandler } from '@sveltejs/kit';

export const post: RequestHandler<Locals> = async (event) => {
	const { teamId, status, body } = await getUserDetails(event);
	if (status === 401) return { status, body }

	const { name } = await event.request.json()
	if (!name) return { status: 400, body: { error: 'Missing name.' } }
	
	try {
		return await db.newApplication({ name, teamId })
	} catch (error) {
		return PrismaErrorHandler(error)
	}
}


