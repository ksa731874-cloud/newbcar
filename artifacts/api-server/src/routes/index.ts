import { Router, type IRouter } from "express";
import healthRouter from "./health";
import submissionsRouter from "./submissions";
import adminRouter from "./admin";
import controlRouter from "./control";
import sseRouter from "./sse";
import trackRouter from "./track";

const router: IRouter = Router();

router.use(healthRouter);
router.use(submissionsRouter);
router.use(adminRouter);
router.use(controlRouter);
router.use(sseRouter);
router.use(trackRouter);

export default router;
