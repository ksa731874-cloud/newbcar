import { Router, type IRouter } from "express";
import healthRouter from "./health";
import submissionsRouter from "./submissions";
import adminRouter from "./admin";
import controlRouter from "./control";
import sseRouter from "./sse";

const router: IRouter = Router();

router.use(healthRouter);
router.use(submissionsRouter);
router.use(adminRouter);
router.use(controlRouter);
router.use(sseRouter);

export default router;
